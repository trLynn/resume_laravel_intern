<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Document</title>
    <style>
        * {
            margin: 0;
            margin-right: 1em;
            font-family: 'zawgyi', 'Times New Roman';
            line-height: 1.5em;
        }

        body {
            width: 100%;
            height: 100%;
            background-color: aliceblue;
        }


        .left-col {
            float: left;
            width: 50%;
            height: 100%;
            padding-left: 10px;
            padding-right: 10px;
            word-break: break-word;
        }

        .right-col {
            float: right;
            width: 40%;
            height: 100%;
            padding-left: 10px;
            padding-right: 10px;
            word-break: break-word;
        }

        .layout2 {
            background-color: #5D8389;
        }

        .image {
            border-radius: 150px;
            margin-bottom: 0.75em;
            background-color: aliceblue;
            width: 150px;
            height: 150px;
        }

        .image-style {
            text-align: center !important;
            padding-top: 25px;
        }

        .name {
            padding:0.5em;
            font-size: 20pt;
            font-weight: bold;
            text-align: center;
            font-family:  'Helvetica', 'zawgyi';
        }

        .heading {
            margin-bottom: 1.1em;
            font-size: 16pt;
            font-weight: bold;
            font-family:  'Helvetica', 'zawgyi';
            text-align: left;
        }

        .heading-bottom {
            font-size:16pt;
            margin-bottom: 0.3em;
            font-family: 'Helvetica' , 'zawgyi';
            border-bottom: 1px solid #1d7afb;
        }
        .heading-bottom-white {
            font-size:16pt;
            margin-bottom: 0.3em;
            font-family: 'Helvetica' , 'zawgyi';
            border-bottom: 1px solid white;
        }
        .heading-bottom-1 {
            font-size:16pt;
            margin-bottom: 0.3em;
            font-family: 'Helvetica' , 'zawgyi';
        }

        .font-color {
            color: #1d7afb;
        }

        .padding {
            margin-top:0.75em;
            padding-top: 2em;
        }

        .subheading {
            font-size: 12pt;
            font-weight: 300;
            text-align: left;
            font-family: 'Times New Roman', times, 'zawgyi';
            word-break: break-word;
        }

        .white {
            color: white;
        }

        .profile_img {
            margin-top: 15px;
            margin-left: 4.5em;
            position: absolute;
            width: 170px;
            height: 170px;
            border-radius: 170px;
            border-style: solid;
            border-color: white;
            border-width: medium;
            background-color: aliceblue;
            overflow: hidden;

            background-size: 170px 170px;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
        }

        .default-image {
            padding: 15px;
            background-image:url("{{public_path().'/img/profile.jpg'}}");
            width: 170px;
            height: 170px;
            border-radius: 170px;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
        }

        .real-image {
            background-image:url("{{storage_path().'/app/public/'.$image}}");
            width: 170px;
            height: 170px;
            border-radius: 170px;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
        }

        .writable-area {
            padding-left:2em;
            margin-left:0.30em;
            overflow-wrap: break-word;
        }
        .writeable{
            padding:2em;
        }
        .writeable-top{
            padding-top:1em;
        }
        .bold{
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="left-col writable-area writeable-top">
        <div class="padding" style="border-radius:50%;">
            <!-- loop all the form -->
            @for($i=2; $i<count($body);$i++) <!-- loop around the all the data -->
                <!-- loop -->
                @if($body[$i]['type_id']!=config('TWO',2) and$body[$i]['type_id']!=config("SEVEN"))
                <div class="heading">
                    <div class="heading-bottom font-color">
                        {{$body[$i]['heading_name']}}

                    </div>
                    <div class="subheading">
                        {{$body[$i]['value']}}
                    </div>
                </div>

                @elseif($body[$i]['type_id']==config('TWO',2))
                <!--if data is multichoice-->
                <div class="heading">
                    <div class="heading-bottom font-color">
                        {{$body[$i]['heading_name']}}

                    </div>
                    <div class="subheading">
                        @if(!empty($body[$i]['value']))
                        <!-- the data is not empty -->
                        <ul>
                            @foreach($body[$i]['value'] as $r)
                            @if(!empty($r['check']) and !empty($r['level_name']))
                            <li>{{$r['subName']}}&nbsp;({{$r['level_name']}})</li>
                            @elseif(!empty($r['check']) and empty($r['level_name']))
                            <li>{{$r['subName']}}</li>
                            @endif
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
                @elseif($body[$i]['type_id']==config("SEVEN"))
                <!-- if the data is attach file -->
                <div class="heading">
                    <div class="heading-bottom font-color">
                        {{$body[$i]['heading_name']}}

                    </div>
                    @if(!empty($body[$i]['value']))
                    <!-- show all attach files -->
                    <div class="subheading">
                        <ul>
                            @foreach($body[$i]['value'] as $r)
                            <li>{{explode('/',$r)[count(explode('/',$r))-1]}}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
                @endif
                @endfor
        </div>
    </div>
    <div class="right-col layout2 ">
        <div style="padding-top:20px">
            <div class="profile_img">
                @empty($image)
                <!-- default image -->
                <img src="{{public_path().'/img/profile.png'}}" class="default-image" />
                @else
                <!-- real image-->
                <div class="real-image"></div>
                @endempty
            </div>

            <div class="name">
                <div class="heading-bottom-1 white">
                @isset($name_dict['name']['value'])
                    @if(is_array($name_dict['name']['value']))
                        <ul>
                        @foreach($name_dict['name']['value'] as $k)
                            @if($name_dict['name']['type_id']!= config("TWO"))
                                <li style="list-style-type: none;color:white;">{{$k}}</li>
                            @else
                                    @if(!empty($k['check']) and isset($k['level_name']))
                                    <li style="list-style-type: none;color:white;">{{$k['subName']}} &nbsp;({{$k['level_name']}})</li>
                                    @elseif(!empty($k['check']) and !isset($k['level_name']))
                                    <li style="list-style-type: none;color:white;">{{$k['subName']}}</li>
                                    @endif
                            @endif
                        @endforeach
                        </ul>
                    @else
                        {{$name_dict['name']['value']}}
                    @endif
                @endisset
                </div>
            </div>
            <div class="writeable">
                <div class="heading white">
                    @if(!empty($head))
                    <div class="heading-bottom-white">
                        Contact
                    </div>
                    <div class='subheading'>
                        @foreach($head as $k)
                        <div style="overflow-wrap: break-word;" class="white">
                            <div class='bold' style="font-size:13pt;">{{$k['heading_name']}}:</div>
                            @if(is_array($k['value']))
                            <ul>
                                @foreach($k['value'] as $s)
                                @if($k['type_id']!= config("TWO"))
                                <span style="word-break:break-all;white-space: normal;color:white;">{{$s}} <br /></span>
                                @else
                                    @if(!empty($s['check']) and isset($s['level_name']))
                                    <span style="word-break:break-all;white-space: normal;color:white;">{{$s['subName']}} &nbsp;({{$s['level_name']}}) <br /></span>
                                    @elseif(!empty($s['check']) and !isset($s['level_name']))
                                    <span style="word-break:break-all;white-space: normal;color:white;">{{$s['subName']}} <br /></span>
                                    @endif
                                @endif
                                @endforeach
                            </ul>
                            @else
                            <span style="word-break:break-all;white-space: normal;color:white;">{{$k['value']}}</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>


                @for($i=0; $i<2;$i++) @if(count($body)>$i)
                    @if($body[$i]['type_id']!=config('TWO',2) and $body[$i]['type_id']!=config("SEVEN"))
                    <!-- if the data is not attachfile or multichoice -->
                    <div class="heading white">
                        <div class="heading-bottom-white">
                            {{$body[$i]['heading_name']}}

                        </div>
                        <div class="subheading">
                            {{$body[$i]['value']}}
                        </div>
                    </div>

                    @elseif($body[$i]['type_id']==config('TWO',2))
                    <!-- if data is multichoice -->
                    <div class="heading white">
                        <div class="heading-bottom-white">
                            {{$body[$i]['heading_name']}}

                        </div>
                        <div class="subheading">
                            @if(!empty($body[$i]['value']))
                            <ul>
                                @foreach($body[$i]['value'] as $r)
                                @if(!empty($r['check']) and !empty($r['level_name']))
                                <li>
                                    {{$r['subName']}}({{$r['level_name']}})
                                </li>
                                @elseif(!empty($r['check']) and empty($r['level_name']))
                                <li>
                                    {{$r['subName']}}
                                </li>
                                @endif
                                @endforeach
                            </ul>
                            @endif
                        </div>
                    </div>
                    @elseif($body[$i]['type_id']==config("SEVEN"))
                    <!-- The type id is attachfile -->
                    <div class="heading white">
                        <div class="heading-bottom-white">
                            {{$body[$i]['heading_name']}}

                        </div>
                        @if(!empty($body[$i]['value']))
                        <div class="subheading">
                            <ul>
                                @foreach($body[$i]['value'] as $r)
                                <li>{{explode('/',$r)[count(explode('/',$r))-1]}}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                    @endif
                    @endif
                    @endfor
            </div>
        </div>
    </div>
</body>

</html>
