<?php

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConstantCollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("constant_collections")->truncate();
        DB::table("constant_collections")->insert(array(

                array(
                    "constant_name" => 'PAGING_LIMIT_40',
                    "constant_definition" => '40',
                    "remark" => 'ALL',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'PAGING_LIMIT_20',
                    "constant_definition" => '20',
                    "remark" => 'ALL',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'ZERO',
                    "constant_definition" => '0',
                    "remark" => 'if condition check with zero.',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'ONE',
                    "constant_definition" => '1',
                    "remark" => 'if condition check with one.',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'TWO',
                    "constant_definition" => '2',
                    "remark" => 'if condition check with two.',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'THREE',
                    "constant_definition" => '3',
                    "remark" => 'if condition check with three.',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'FOUR',
                    "constant_definition" => '4',
                    "remark" => 'if condition check with four.',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'FIVE',
                    "constant_definition" => '5',
                    "remark" => 'if condition check with five.',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'SIX',
                    "constant_definition" => '6',
                    "remark" => 'if condition check with six.',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'SEVEN',
                    "constant_definition" => '7',
                    "remark" => 'if condition check with seven.',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'EIGHT',
                    "constant_definition" => '8',
                    "remark" => 'if condition check with eight.',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'NINE',
                    "constant_definition" => '9',
                    "remark" => 'Number 9.',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'TEN',
                    "constant_definition" => '10',
                    "remark" => 'Number 10.',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'HTTP_CODE_200',
                    "constant_definition" => 200,
                    "remark" => ' Success HTTP Status Code',
                    "created_emp" => '10001',
                    "updated_emp" => '10001',
                    "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
                ),
                array(
                    "constant_name" => 'HTTP_CODE_500',
                    "constant_definition" => 500,
                    "remark" => ' Error HTTP Status Code',
                    "created_emp" => '10001',
                    "updated_emp" => '10001',
                    "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
                ),
                array(
                    "constant_name" => 'HTTP_CODE_422',
                    "constant_definition" => 422,
                    "remark" => ' Error HTTP Status Code',
                    "created_emp" => '10001',
                    "updated_emp" => '10001',
                    "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
                ),
                array(
                    "constant_name" => 'Create_Link',
                    "constant_definition" => '/template/user-login/',
                    "remark" => 'Create Link',
                    "created_emp" => '10001',
                    "updated_emp" => '10001',
                    "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
                ),
                array(
                    "constant_name" => 'WEB',
                    "constant_definition" => '1',
                    "remark" => 'web',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'ANDROID',
                    "constant_definition" => '2',
                    "remark" => 'device_flave , 1=web,2=android,3=ios',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'IOS',
                    "constant_definition" => '3',
                    "remark" => 'IOS',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'SAVE',
                    "constant_definition" => '1',
                    "remark" => 'save',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'UPDATE',
                    "constant_definition" => '2',
                    "remark" => 'update',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'DELETE',
                    "constant_definition" => '3',
                    "remark" => 'delete',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'DOWNLOAD',
                    "constant_definition" => '4',
                    "remark" => 'download',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'UPLOAD',
                    "constant_definition" => '5',
                    "remark" => 'upload',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'LEVEL_CATEGORY_ALPHABET',
                    "constant_definition" => '1',
                    "remark" => 'Level Category Id for Alphabet',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'LEVEL_CATEGORY_NUMBER',
                    "constant_definition" => '2',
                    "remark" => 'Level Category Id for Number',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'LEVEL_CATEGORY_TEXT',
                    "constant_definition" => '3',
                    "remark" => 'Level Category Id for Average, Medium, Best',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'LEVEL_NONE',
                    "constant_definition" => '0',
                    "remark" => 'Id for Unselected Level',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'HEADING_TYPE_CHECKBOX',
                    "constant_definition" => '2',
                    "remark" => 'Heading Type of Multiple Choice',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'HEADING_TYPE_DATA_LIST',
                    "constant_definition" => '1',
                    "remark" => 'Heading Type of Data List',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'HEADING_TYPE_SINGLE_CHOICE',
                    "constant_definition" => '3',
                    "remark" => 'Heading Type of Single Choice',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'MAX_SINGLE_VALUE_TEXT_LENGTH',
                    "constant_definition" => '255',
                    "remark" => 'Heading Type of Single Choice',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'MAX_ATTACHMENT_FILENAME_LENGTH',
                    "constant_definition" => '200',
                    "remark" => 'Heading Type of Single Choice',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'MAX_COMMENT_LENGTH',
                    "constant_definition" => '1500',
                    "remark" => 'Heading Type of Single Choice',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'MAX_IMAGE_SIZE',
                    "constant_definition" => '10',
                    "remark" => 'Heading Type of Single Choice',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'MAX_ATTACHMENT_SIZE',
                    "constant_definition" => '30',
                    "remark" => 'Attachment file size validation',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
                array(
                    "constant_name" => 'THIRTY',
                    "constant_definition" => '10',
                    "remark" => 'Attachment file ammount validation',
                    "created_emp" => '1',
                    "updated_emp" => '1',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                ),
            ));

    }
}
