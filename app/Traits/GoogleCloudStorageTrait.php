<?php

namespace App\Traits;

use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * To access google cloud storage data
 *
 * @author	thiri win htwe
 * @create	2022/06/17
 */
trait GoogleCloudStorageTrait
{
    /**
     * Connect to google cloud storage
     *
     * @author	thiri win htwe
     * @create	2022/06/17
     * @return	array
     */
    public function connectGoogleCloudStorage()
    {
        try {
            # Your Google Cloud Platform project ID and key file path
            $projectId = config('app.project_id');
            $keyFileName = config('app.key_file_path');
            $keyFilePath = storage_path() .'/'. $keyFileName;

            # Instantiates a client
            $storage = new StorageClient([
                'projectId' => $projectId,
                'keyFilePath' => $keyFilePath
            ]);

            # The name of the bucket
            $bucketName = config('app.bucket');

            return array($storage, $bucketName);

        } catch (GoogleException $e) {
            Log::channel('debuglog')->debug($e->getMessage() . ' in file ' . __FILE__ . ' at line ' . __LINE__ . ' within the class ' . get_class());
            return false;
        }
    }

    /**
     * Upload object to cloud Storage
     *
     * @author	thiri win htwe
     * @create	2022/06/17
     * @param	$file, $objectName
     * @return	boolean
     */
    public function uploadObject($file, $objectName)
    {
        $cloud = $this->connectGoogleCloudStorage();
        # check connect with Google Cloud Storage or not
        if ($cloud) {
            $storage = $cloud[0];
            $bucketName = $cloud[1];

            $bucket = $storage->bucket($bucketName);
            try {
                $object = $bucket->upload($file,  [
                    'name' => $objectName
                ]);
                return true;
            } catch (GoogleException $e) {
                Log::channel('debuglog')->debug($e->getMessage() . ' in file ' . __FILE__ . ' at line ' . __LINE__ . ' within the class ' . get_class());
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Download multiple file to a specific foler under storage folder [eg: storage/temp_YmdHis]
     *
     * @author	thiri win htwe
     * @create	2022/06/17
     * @param	fileData (must be array)
     * @return	temporary folder/file name [should delete this folder/file after working with it]
     */
    public function downloadMultipleObjects($fileData)
    {
        $tmp = '';
        try {
            $cloud = $this->connectGoogleCloudStorage();

            # check connect with Google Cloud Storage or not
            if ($cloud) {
                $storage = $cloud[0];
                $bucketName = $cloud[1];
                $bucket = $storage->bucket($bucketName);
                # check download multiple files or single file
                if (count($fileData) > 1) {
                    $tmp = 'temp_' . date('YmdHis');
                    # create temporary directory
                    Storage::makeDirectory($tmp);
                   
                    foreach ($fileData as $link) {
                        $cloudFilePath = $link['attach_files'];
                        $fileName = substr(strrchr($cloudFilePath, "/"), 1);
                        $object = $bucket->object($cloudFilePath);
                        # get cloud storage file as stream
                        $stream = $object->downloadAsStream();
                        # get file content from stream
                        $file = $stream->getContents();
                        # file path to store data
                        $tmpPath = $tmp . '/' . $fileName;
                        # save cloud stream as file under storage/temp_YmdHis folder
                        Storage::put($tmpPath, $file);
                    }
                } else {
                    foreach ($fileData as $link) {
                        $cloudFilePath = $link['attach_files'];
                        # get file name
                        $tmp = substr(strrchr($cloudFilePath, "/"), 1);
                        $object = $bucket->object($cloudFilePath);
                        # get cloud storage file as stream
                        $stream = $object->downloadAsStream();
                        # get file content from stream
                        $file = $stream->getContents();
                        # save cloud stream as file under storage/app folder
                        Storage::put($tmp, $file);
                    }
                }
                return $tmp;
            } else {
                return false;
            }
        } catch (GoogleException $e) {
            Log::channel('debuglog')->debug($e->getMessage() . ' in file ' . __FILE__ . ' at line ' . __LINE__ . ' within the class ' . get_class());
            return false;
        }
    }

    /**
     * Delete object from google cloud storage
     *
     * @author	thiri win htwe
     * @create	2022/06/17
     * @param	cloud storage path
     * @return	boolean
     */
    public function deleteObject($url)
    {
        try {
            $cloud = $this->connectGoogleCloudStorage();
            $storage = $cloud[0];
            $bucketName = $cloud[1];
            $bucket = $storage->bucket($bucketName);
            $object = $bucket->object($url);
            if ($object->exists()) {
                $object->delete();
            }
            return true;
        } catch (GoogleException $e) {
            Log::channel('debuglog')->debug($e->getMessage() . ' in file ' . __FILE__ . ' at line ' . __LINE__ . ' within the class ' . get_class());
            return false;
        }
    }

    /**
     * Generate a v4 signed URL for downloading an object.
     * By using this url, can show image directly in web page without downloading image into server.
     *
     * @author	thiri win htwe
     * @create	2022/06/17
     * @param 	string $objectName the name of your Google Cloud object.
     * @return 	url
     */
    function objectV4SignedUrl($objectName)
    {
        # $objectName must be file path. eg: $objectName = 'picture/2019/06/flower.jpg';
        $cloud = $this->connectGoogleCloudStorage();
        $storage = $cloud[0];
        $bucketName = $cloud[1];
        $bucket = $storage->bucket($bucketName);
        $object = $bucket->object($objectName);
        $url = $object->signedUrl(
            # This URL is valid for 15 minutes
            new \DateTime('30 min'),
            [
                'version' => 'v4'
            ]
        );
        # example code to show image in web page
        # <img src="$url">
        return $url;
    }
}