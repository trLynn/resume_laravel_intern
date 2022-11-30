<?php


namespace App\Traits;

use Meneses\LaravelMpdf\Facades\LaravelMpdf as Pdf;
use Rabbit;
use \Exception;

/**
 * This will generate the format of the pdf template.
 * @author Thu Ta
 * @create_date 20/6/2022
 */
trait PdfFormatGeneratorTrait
{
    private array $firstNames = ['name', 'first name', 'firstname', 'first_name', 'givenname', 'given name', 'given_name'];
    private array $lastNames = ['surname', 'sur name', 'sur_name', 'last name', 'lastname', 'last_name', 'givenname', 'primaryname', 'primary name', 'primary_name'];

    private array $heading = ['name', 'email', 'address', 'phone', 'profile'];
    private string $footer = 'link';
    /**
     * This function will generate head and body fields and types.
     * @param int $layout_id
     * @param array $data (This is the data from the form filled from User)
     * @author Thu Ta
     * @create_date 20/6/2022
     * @return string $filename
     */
    function generate(int $layout_id, array $data, $image)
    {
        if ($layout_id == config("ONE")) { # layout array for layout one
            $formatted_data = $this->generateLayout1($data, empty($image) ? null : $image);

            $pdf = Pdf::loadView("pdf_layouts/layout1", $formatted_data);
        }
        elseif ($layout_id == config("TWO")) { # layout array for layout two
            $formatted_data = $this->generateLayout2($data, empty($image) ? null : $image);
            $pdf = Pdf::loadView("pdf_layouts/layout2", $formatted_data);
        }
        else { # layout array for layout three
            $formatted_data = $this->generateLayout2($data, empty($image) ? null : $image);
            $pdf = Pdf::loadView("pdf_layouts/layout3sample", $formatted_data);
        }
        $filename = "PDF" . date("Ymdhis") . ".pdf";
        $pdf->save(storage_path("app/" . $filename));
        return $filename;
    }
    /**
     *This will generate the format of the pdf template.
     * @author Thu Ta
     * @param array data
     * @param mixed $image
     * @return array
     */
    private function generateLayout1(array $data, $image)
    {
        $name = "";
        $name_dict = [];
        $head = [];
        $body = [];
        for ($i = 0; $i < count($data); $i++) { # loop through all data
            // if(in_array(strtolower($data[$i]['heading_name']),$this->firstNames)){
            //     $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
            //     $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
            //     if(isset($name_dict['name'])){
            //         $name_dict['name']=$data[$i]['value'] . $name_dict['name'];
            //     }
            //     $name_dict['name'] = $data[$i]['value'];
            // }
            // elseif(in_array(strtolower($data[$i]['heading_name']),$this->firstNames)){
            //     $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
            //     $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
            //     $name_dict['name'] .= ' ' . $data[$i]['value'];
            // }
            // elseif ($data[$i]['heading_name'] == 'နာမည်') { # check name exists
            //     $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
            //     $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
            //     $name_dict['name'] = $data[$i]['value'];
            // }
            // elseif ($data[$i]['heading_name'] == 'အမည်') { # check name exists
            //     $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
            //     $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
            //     $name_dict['name'] = $data[$i]['value'];
            // }
            if (strtolower($data[$i]['heading_name']) == 'name') {
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    //throw new Exception("Hey Admin You should Consider the reserve words are not multi values.");
                    $name_dict['name'] = $data[$i];
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                $name_dict['name'] = $data[$i];
            }
            elseif ($data[$i]['heading_name'] == 'နာမည်') { # check name exists
                if(is_array($data[$i]['value'])){
                    //throw new Exception("Hey Admin You should Consider the reserve words are not multi values.");
                    $name_dict['name'] = $data[$i];
                    continue;
                }
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                $name_dict['name'] = $data[$i];
            }
            elseif ($data[$i]['heading_name'] == 'အမည်') { # check name exists
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);

                if(is_array($data[$i]['value'])){
                    //throw new Exception("Hey Admin You should Consider the reserve words are not multi values.");
                    $name_dict['name'] = $data[$i];
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                $name_dict['name'] = $data[$i];
            }
            elseif (strpos(strtolower($data[$i]['heading_name']), 'email') !== false) { # check email exists
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    //throw new Exception("Hey Admin You should Consider the reserve words are not multi values.");
                    array_push($head, $data[$i]);
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                array_push($head, $data[$i]);
            }
            elseif (strpos(strtolower($data[$i]['heading_name']), 'အီးမေးလ်') !== false) { # check phone exists
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    //throw new Exception("Hey Admin You should Consider the reserve words are not multi values.");
                    array_push($head, $data[$i]);
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                array_push($head, $data[$i]);
            }
            elseif (strpos(strtolower($data[$i]['heading_name']), 'အီးမေး') !== false) { # check phone exists
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    //throw new Exception("Hey Admin You should Consider the reserve words are not multi values.");
                    array_push($head, $data[$i]);
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                array_push($head, $data[$i]);
            }
            elseif (strpos(strtolower($data[$i]['heading_name']), 'address') !== false) { # check address exists
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    //throw new Exception("Hey Admin You should Consider the reserve words are not multi values.");
                    array_push($head, $data[$i]);
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                array_push($head, $data[$i]);
            }
            elseif (strpos(strtolower($data[$i]['heading_name']), 'လိပ်စာ') !== false) { # check phone exists
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    //throw new Exception("Hey Admin You should Consider the reserve words are not multi values.");
                    array_push($head, $data[$i]);
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                array_push($head, $data[$i]);
            }
            elseif (strpos(strtolower($data[$i]['heading_name']), 'phone') !== false) { # check phone exists
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    //throw new Exception("Hey Admin You should Consider the reserve words are not multi values.");
                    array_push($head, $data[$i]);
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                array_push($head, $data[$i]);
            }
            elseif (strpos(strtolower($data[$i]['heading_name']), 'ဖုန်း') !== false) { # check phone exists
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    //throw new Exception("Hey Admin You should Consider the reserve words are not multi values.");
                    array_push($head, $data[$i]);
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                array_push($head, $data[$i]);
            }
            else { # check value is given by frontend
                if ($data[$i]['type_id'] != config("EIGHT")) { # it is not profile picture
                    $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                    if (is_string($data[$i]['value']) && $data[$i]['type_id'] != config("TWO")) { # if it it is single value
                        $data[$i]['value'] = empty($data[$i]['value']) ? "" : Rabbit::uni2zg($data[$i]['value']);
                    }
                    array_push($body, $data[$i]);
                }
            }
        }
        return [
            "name_dict" => $name_dict,
            "image" => $image,
            "head" => $head,
            "body" => $body,
            "builtIn" => $this->heading
        ];
    }
    /**
     *This will generate the format of the pdf template.
     * @author Thu Ta
     * @create_date 20/6/2022
     * @param array data
     * @param mixed $image
     * @return array
     */
    private function generateLayout2(array $data, $image)
    {
        $name_dict = [];
        $head = [];
        $body = [];

        for ($i = 0; $i < count($data); $i++) { # loop through all data
            // if(in_array(strtolower($data[$i]['heading_name']),$this->firstNames)){
            //     $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
            //     $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
            //     if(isset($name_dict['name'])){
            //         $name_dict['name']=$data[$i]['value'] . $name_dict['name'];
            //     }
            //     $name_dict['name'] = $data[$i]['value'];
            // }
            // elseif(in_array(strtolower($data[$i]['heading_name']),$this->firstNames)){
            //     $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
            //     $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
            //     $name_dict['name'] .= ' ' . $data[$i]['value'];
            // }
            // elseif ($data[$i]['heading_name'] == 'နာမည်') { # check name exists
            //     $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
            //     $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
            //     $name_dict['name'] = $data[$i]['value'];
            // }
            // elseif ($data[$i]['heading_name'] == 'အမည်') { # check name exists
            //     $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
            //     $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
            //     $name_dict['name'] = $data[$i]['value'];
            // }
            if (strtolower($data[$i]['heading_name']) == 'name') {
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    $name_dict['name'] = $data[$i];
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                $name_dict['name'] = $data[$i];
            }
            elseif ($data[$i]['heading_name'] == 'နာမည်') { # check name exists
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    $name_dict['name'] = $data[$i];
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                $name_dict['name'] = $data[$i];
            }
            elseif ($data[$i]['heading_name'] == 'အမည်') { # check name exists
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    $name_dict['name'] = $data[$i];
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                $name_dict['name'] = $data[$i];
            }
            elseif (strpos(strtolower($data[$i]['heading_name']), 'email') !== false) { # check email exists
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    array_push($head, $data[$i]);
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                array_push($head, $data[$i]);
            }
            elseif (strpos(strtolower($data[$i]['heading_name']), 'အီးမေးလ်') !== false) { # check phone exists
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    array_push($head, $data[$i]);
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                array_push($head, $data[$i]);
            }
            elseif (strpos(strtolower($data[$i]['heading_name']), 'အီးမေး') !== false) { # check phone exists
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    array_push($head, $data[$i]);
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                array_push($head, $data[$i]);
            }
            elseif (strpos(strtolower($data[$i]['heading_name']), 'address') !== false) { # check address exists
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    array_push($head, $data[$i]);
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                array_push($head, $data[$i]);
            }
            elseif (strpos(strtolower($data[$i]['heading_name']), 'လိပ်စာ') !== false) { # check phone exists
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    array_push($head, $data[$i]);
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                array_push($head, $data[$i]);
            }
            elseif (strpos(strtolower($data[$i]['heading_name']), 'phone') !== false) { # check phone exists
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    array_push($head, $data[$i]);
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                array_push($head, $data[$i]);
            }
            elseif (strpos(strtolower($data[$i]['heading_name']), 'ဖုန်း') !== false) { # check phone exists
                $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                if(is_array($data[$i]['value'])){
                    array_push($head, $data[$i]);
                    continue;
                }
                $data[$i]['value'] = Rabbit::uni2zg($data[$i]['value']);
                array_push($head, $data[$i]);
            }
            else {
                if ($data[$i]['type_id'] != config("EIGHT")) { # it is not profile picture
                    $data[$i]['heading_name'] = Rabbit::uni2zg($data[$i]['heading_name']);
                    if (is_string($data[$i]['value']) && $data[$i]['type_id'] != config("TWO")) { # if it it is single value
                        $data[$i]['value'] = empty($data[$i]['value']) ? "" : Rabbit::uni2zg($data[$i]['value']);
                    }
                    array_push($body, $data[$i]);
                }
            }
        }

        return [
            "name_dict" => $name_dict,
            "image" => $image,
            "head" => $head,
            "body" => $body,
            "builtIn" => $this->heading
        ];
    }
}
