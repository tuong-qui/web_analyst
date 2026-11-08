<?php

namespace Tests\Feature;

use Database\Factories\UserFactory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\DatasetTestCase;

class ProductImportTest extends DatasetTestCase
{
    public function test_import_accepts_original_product_headers_and_whole_decimal_counts(): void
    {
        DB::table('association_rules')->insert([
            'dataset_id' => 1, 'user_id' => 1, 'antecedent_category_name' => 'Books', 'consequent_category_name' => 'Toys',
        ]);
        $file = UploadedFile::fake()->createWithContent('products.csv', "product_id,product_category_name,product_name_lenght,product_description_lenght,product_photos_qty,product_weight_g,product_length_cm,product_height_cm,product_width_cm\np,books,40.0,287.0,1.0,225.0,16.0,10.0,14.0\n");

        $this->post(route('products.import'), ['file' => $file])->assertSessionHasNoErrors()->assertRedirect(route('products'));

        $this->assertDatabaseHas('products', ['product_id' => 'p', 'product_name_length' => 40, 'product_description_length' => 287, 'product_photos_qty' => 1]);
        $this->assertDatabaseCount('association_rules', 0);
        $this->assertDatabaseHas('datasets', ['dataset_id' => 1, 'rules_version' => 1]);
    }

    public function test_fractional_counts_are_rejected(): void
    {
        DB::table('association_rules')->insert([
            'dataset_id' => 1, 'user_id' => 1, 'antecedent_category_name' => 'Books', 'consequent_category_name' => 'Toys',
        ]);
        $this->post(route('products.import'), ['file' => $this->csv("p,books,40,287,1.5,225,16,10,14\n")])->assertSessionHasErrors('file');

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('association_rules', 1);
        $this->assertDatabaseHas('datasets', ['dataset_id' => 1, 'rules_version' => 0]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('products', function (Blueprint $table) {
            $table->unsignedBigInteger('dataset_id');
            $table->string('product_id', 50);
            $table->string('product_category_name', 100)->nullable();
            foreach (['product_name_length', 'product_description_length', 'product_photos_qty'] as $column) {
                $table->integer($column)->nullable();
            }
            foreach (['product_weight_g', 'product_length_cm', 'product_height_cm', 'product_width_cm'] as $column) {
                $table->decimal($column, 10, 2)->nullable();
            }
            $table->primary(['dataset_id', 'product_id']);
        });
    }

    private function csv(string $rows): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('products.csv', "\xEF\xBB\xBFproduct_id,product_category_name,product_name_length,product_description_length,product_photos_qty,product_weight_g,product_length_cm,product_height_cm,product_width_cm\n".$rows);
    }

    public function test_guests_cannot_view_or_import_products(): void
    {
        $this->actingAsGuest();
        $this->get(route('products'))->assertRedirect(route('login'));
        $this->post(route('products.import'))->assertRedirect(route('login'));
        $this->assertDatabaseCount('products', 0);
    }

    public function test_other_users_dataset_cannot_be_read_or_imported(): void
    {
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 2, 'name' => 'Private']);

        $this->get(route('products', ['dataset_id' => 2]))->assertNotFound();
        $this->post(route('products.import'), ['dataset_id' => 2, 'file' => $this->csv("shared,books,,,,,,,\n")])->assertNotFound();
        $this->assertDatabaseCount('products', 0);
    }

    public function test_same_product_id_can_exist_in_separate_datasets(): void
    {
        DB::table('datasets')->insert(['dataset_id' => 2, 'user_id' => 1, 'name' => 'Second shop']);
        $this->post(route('products.import'), ['dataset_id' => 1, 'file' => $this->csv("shared,first-category,,,,,,,\n")])->assertSessionHasNoErrors();
        $this->post(route('products.import'), ['dataset_id' => 2, 'file' => $this->csv("shared,second-category,,,,,,,\n")])->assertSessionHasNoErrors();

        $this->get(route('products'))->assertOk()->assertSee('second-category')->assertDontSee('first-category')
            ->assertViewHas('datasetName', 'Second shop')->assertSee('Second shop');
        $this->assertDatabaseCount('products', 2);
        $this->assertDatabaseHas('products', ['dataset_id' => 2, 'product_id' => 'shared']);
    }

    public function test_user_without_dataset_is_directed_to_create_one(): void
    {
        $this->actingAs(UserFactory::new()->make(['user_id' => 99]));

        $this->get(route('products'))->assertRedirect(route('data'));
    }

    public function test_existing_product_is_preserved_when_import_is_rejected(): void
    {
        DB::table('products')->insert(['dataset_id' => 1, 'product_id' => 'existing', 'product_category_name' => 'original']);

        $this->post(route('products.import'), ['file' => $this->csv("new,books,,,,,,,\nexisting,changed,,,,,,,\n")])->assertSessionHasErrors('file');

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseHas('products', ['product_id' => 'existing', 'product_category_name' => 'original']);
    }

    public function test_empty_data_and_malformed_rows_are_rejected(): void
    {
        foreach (['', "short,row\n", "p,books,1,2,3,1.234,10,20,30\n"] as $rows) {
            $this->post(route('products.import'), ['file' => $this->csv($rows)])->assertSessionHasErrors('file');
            $this->assertDatabaseCount('products', 0);
        }
    }

    public function test_preview_escapes_uploaded_category(): void
    {
        $this->post(route('products.import'), ['file' => $this->csv("safe,<script>alert(1)</script>,,,,,,,\n")])->assertSessionHasNoErrors();

        $this->get(route('products'))->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_import_saves_products_and_displays_database_preview(): void
    {
        $this->followingRedirects()->post(route('products.import'), ['file' => $this->csv("product-1,books,12,34,1,200.50,10,20,30\nproduct-2,books,,,,,,,\n")])
            ->assertOk()->assertSessionHasNoErrors()->assertSee('product-1')->assertSee('books');
        $this->assertDatabaseCount('products', 2);
        $this->assertDatabaseHas('products', ['product_id' => 'product-2', 'product_weight_g' => null]);
    }

    public function test_invalid_row_rolls_back_import(): void
    {
        $this->post(route('products.import'), ['file' => $this->csv("product-1,books,12,34,1,200,10,20,30\nproduct-2,books,-1,34,1,200,10,20,30\n")])
            ->assertSessionHasErrors('file');
        $this->assertDatabaseCount('products', 0);
    }

    public function test_duplicate_id_rolls_back_import(): void
    {
        $this->post(route('products.import'), ['file' => $this->csv("product-1,books,,,,,,,\nproduct-1,books,,,,,,,\n")])
            ->assertSessionHasErrors('file');
        $this->assertDatabaseCount('products', 0);
    }

    public function test_missing_headers_are_rejected(): void
    {
        $file = UploadedFile::fake()->createWithContent('products.csv', "product_id\nproduct-1\n");
        $this->post(route('products.import'), ['file' => $file])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('products', 0);
    }

    public function test_preview_contains_at_most_100_products(): void
    {
        for ($i = 1; $i <= 101; $i++) {
            DB::table('products')->insert(['dataset_id' => 1, 'product_id' => sprintf('product-%03d', $i), 'product_category_name' => 'unique-'.$i]);
        }
        $this->get(route('products'))->assertOk()
            ->assertViewHas('products', fn ($rows) => $rows->count() === 100)
            ->assertSee('product-100')->assertDontSee('product-101');
    }

    public function test_large_import_uses_batch_queries(): void
    {
        $rows = '';
        for ($i = 1; $i <= 1001; $i++) {
            $rows .= "product-{$i},books,12,34,1,200,10,20,30\n";
        }
        DB::enableQueryLog();
        $this->post(route('products.import'), ['file' => $this->csv($rows)])->assertSessionHasNoErrors();
        $queries = collect(DB::getQueryLog())->filter(fn ($query) => str_contains($query['query'], 'products'));
        DB::disableQueryLog();
        $this->assertCount(6, $queries);
        $this->assertDatabaseCount('products', 1001);
    }

    public function test_duplicate_after_first_batch_rolls_back_all_rows(): void
    {
        $rows = '';
        for ($i = 1; $i <= 500; $i++) {
            $rows .= "product-{$i},books,12,34,1,200,10,20,30\n";
        }
        $rows .= "product-1,books,12,34,1,200,10,20,30\n";
        $this->post(route('products.import'), ['file' => $this->csv($rows)])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('products', 0);
    }
}
