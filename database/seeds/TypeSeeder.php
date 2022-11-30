<?php

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("types")->truncate();
        DB::table("types")->insert(array(
            array(
                "name"                 =>  "DataList",
                "created_at"           =>  Carbon::now()->format("Y-m-d H:i:s"),
                "updated_at"           =>  Carbon::now()->format("Y-m-d H:i:s")
            ),
            array(
                "name"                 =>  "Multiple Choice",
                "created_at"           =>  Carbon::now()->format("Y-m-d H:i:s"),
                "updated_at"           =>  Carbon::now()->format("Y-m-d H:i:s")
            ),
            array(
                "name"                 =>  "Single Choice",
                "created_at"           =>  Carbon::now()->format("Y-m-d H:i:s"),
                "updated_at"           =>  Carbon::now()->format("Y-m-d H:i:s")
            ),
            array(
                "name"                 =>  "Textbox",
                "created_at"           =>  Carbon::now()->format("Y-m-d H:i:s"),
                "updated_at"           =>  Carbon::now()->format("Y-m-d H:i:s")
            ),
            array(
                "name"                 =>  "Comment Box",
                "created_at"           =>  Carbon::now()->format("Y-m-d H:i:s"),
                "updated_at"           =>  Carbon::now()->format("Y-m-d H:i:s")
            ),
            array(
                "name"                 =>  "Date Time",
                "created_at"           =>  Carbon::now()->format("Y-m-d H:i:s"),
                "updated_at"           =>  Carbon::now()->format("Y-m-d H:i:s")
            ),
            array(
                "name"                 =>  "Attach File",
                "created_at"           =>  Carbon::now()->format("Y-m-d H:i:s"),
                "updated_at"           =>  Carbon::now()->format("Y-m-d H:i:s")
            ),
            array(
                "name"                 =>  "Profile Image",
                "created_at"           =>  Carbon::now()->format("Y-m-d H:i:s"),
                "updated_at"           =>  Carbon::now()->format("Y-m-d H:i:s")
            )

        ));
    }
}
