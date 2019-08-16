<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permissions')->delete();
        DB::table('permissions')->insert([
            [
                'icon_name' => 'add',
                'icon_color' => 'green',
                'name' => 'products_add',
                'slug' => '添加商品',
                'created_at' => '2019-08-16 17:00:00',
                'updated_at' => '2019-08-16 17:00:00',
            ],
            [
                'icon_name' => 'edit',
                'icon_color' => 'blue',
                'name' => 'products_edit',
                'slug' => '编辑商品',
                'created_at' => '2019-08-16 17:00:00',
                'updated_at' => '2019-08-16 17:00:00',
            ],
            [
                'icon_name' => 'delete',
                'icon_color' => 'red',
                'name' => 'products_delete',
                'slug' => '删除商品',
                'created_at' => '2019-08-16 17:00:00',
                'updated_at' => '2019-08-16 17:00:00',
            ],
        ]);
    }
}
