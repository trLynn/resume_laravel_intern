<?php

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("levels")->truncate();
        DB::table("levels")->insert([
                [
                    'level_category_id' => 3,
                    'level'         => 'Beginner',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 3,
                    'level'         => 'Intermediate',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 3,
                    'level'         => 'Advanced',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'A',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'B',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'C',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'D',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'E',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'F',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'G',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'H',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'I',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'J',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'K',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'L',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'M',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'N',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'O',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'P',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'Q',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'R',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'S',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'T',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'U',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'V',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'W',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'X',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'Y',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'level_category_id' => 1,
                    'level'         => 'Z',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],

        ]);
    }
}
