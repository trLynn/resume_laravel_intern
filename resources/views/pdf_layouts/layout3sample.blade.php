<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        @page {
            margin: 60px 0px;
        }

        header {
            position: fixed;
            top: -60px;
            height: 50px;
            text-align: center;
            line-height: 35px;
            width: 100%;
        }

        * {
            padding: 0;
        }

        footer {
            position: fixed;
            bottom: -60px;
            height: 50px;
            line-height: 35px;
            width: 100%;
            display: inline;
        }

        .layout3 {
            background-color: #8993EF;
        }

        .block {
            height: 50px;
        }

        .w-35 {
            width: 35%;
        }

        .w-59 {
            width: 65%;
        }

        .left-col {
            float: left;
            padding: 1em;
            width: 35%;
            word-break: break-word;
        }

        .right-col {
            float: right;
            padding: 1.5em;
            width: 55%;
            word-break: break-word;

        }

        body,
        html {
            height: 100%;
        }

        @font-face {
            font-family: "ZawGyi";
            font-weight: normal;
            font-size: medium;
            src:url("{{storage_path('font/ZawgyiOne2008.ttf')}}") format("truetype")
        }

        body {
            font-family: "zawgyi", times;
        }

        .real-image {
            background-image:url("{{storage_path().'/app/public/'.$image}}");
            width: 170px;
            height: 170px;
            border-radius: 170px;
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

        .name {
            text-align: center;
            font-size: 20pt;
            margin-top: 25px;
            margin-bottom: 22px;
            font-family: 'zawgyi';
        }

        .heading {
            /* margin: 0.5em;
            padding: 0.25em; */
            margin: 10px 15px 10px;
            font-size: 16pt;
            font-weight: bolder;
            font-family: 'Helvetica' , 'zawgyi';
        }

        .subheading {
            /* margin-top: 0.5em;
            margin-top: 0.5em;
            padding-top: 0.25em;
            padding-bottom: 0.25em; */
            font-size: 12pt;
            font-family:'Times New Roman', times, 'zawgyi';
        }

        .w-50 {
            width: 50%;
        }

        .h-100 {
            height: 100%;
        }

        .w-100 {
            width: 100%;
        }

        .bold {
            font-weight: bold;
        }

        .light {
            font-weight: lighter;
        }

        .font-color-layout3 {
            color: #404aa3;
        }

        .profile_img {
            margin-top: 15px;
            margin-left: 55px;
            position: absolute;
            width: 170px;
            height: 170px;
            border-radius: 170px;
            border-style: solid;
            border-color: white;
            border-width: medium;
            overflow: hidden;
            background-size: 200px 200px;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
        }

        .border-bottom{
            border-bottom: 1px solid #404aa3;
        }

        .margin {
            margin-right: 1em;
            margin-left: 1em;
        }

        .writable-area {
            padding-left: 1em;
            margin-left: 1.5em;
        }

        .overflow-warp {
            overflow-wrap: break-word;
        }

        @page {
            size: auto;
            odd-header-name: MyHeader1;
            odd-footer-name: MyFooter1;
        }
    </style>
</head>

<body>
    <htmlpageheader name="MyHeader1">
        <table width="100%">
            <tr>
                <td width="35%" style="text-align: right; height:50px;" class="layout3"></td>
                <td width="55%"></td>
            </tr>
        </table>
    </htmlpageheader>
    <main class="h-100">
        <div class="left-col center overflow-warp">
            {{-- profile image --}}
            <div class="profile_img">
                @empty($image)
                <img src="{{public_path().'/img/profile.png'}}" class="default-image" />
                @else
                <div class='real-image'></div>
                @endempty
            </div>
            {{-- Name --}}
            <div class="name font-color-layout3">
                @isset($name_dict['name']['value'])
                    @if(is_array($name_dict['name']['value']))
                        <ul>
                        @foreach($name_dict['name']['value'] as $k)
                            @if($name_dict['name']['type_id']!= config("TWO"))
                                <li style="list-style-type: none;" class="font-color-layout3">{{$k}}</li>
                            @else
                                    @if(!empty($k['check']) and isset($k['level_name']))
                                    <li style="list-style-type: none;" class="font-color-layout3" >{{$k['subName']}} &nbsp;({{$k['level_name']}})</li>
                                    @elseif(!empty($k['check']) and !isset($k['level_name']))
                                    <li style="list-style-type: none;" class="font-color-layout3">{{$k['subName']}}</li>
                                    @endif
                            @endif
                        @endforeach
                        </ul>
                    @else
                        {{$name_dict['name']['value']}}
                    @endif
                @endisset
            </div>
            {{-- Contact --}}
            <div style="line-height: 1.5em;">
                <div class="heading ">
                    @if(!empty($head))
                    <div class='border-bottom'>
                        <b class="font-color-layout3 " style="width:100%;">Contact </b>
                    </div>
                    <div class='subheading' style="padding: 10px;">
                        @foreach($head as $k)
                        <div style="overflow-wrap: break-word;margin:7px;">
                            <b>{{$k['heading_name']}}: </b>
                            <div>
                            @if(is_array($k['value']))
                            <ul>
                            @foreach($k['value'] as $s)
                                @if($k['type_id']!= config("TWO"))
                                <li style="list-style-type: none;">{{$s}}</li>
                                @else
                                    @if(!empty($s['check']) and isset($s['level_name']))
                                    <li style="list-style-type: none;">{{$s['subName']}} &nbsp;({{$s['level_name']}})</li>
                                    @elseif(!empty($s['check']) and !isset($s['level_name']))
                                    <li style="list-style-type: none;">{{$s['subName']}}</li>
                                    @endif
                                @endif
                            @endforeach
                            </ul>
                            @else
                                {{$k['value']}}
                            @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                @for($i=0; $i<2;$i++) @if(count($body)>$i)
                    {{-- Except type 2 and type 7 --}}
                    @if($body[$i]['type_id']!=config('TWO',2) and $body[$i]['type_id']!=config("SEVEN"))
                    <div class="heading">
                        <div class="font-color-layout3 border-bottom">
                            <b>{{$body[$i]['heading_name']}}</b>
                        </div>
                        <div class="subheading" style="padding: 10px;margin: 7px;">
                            {{$body[$i]['value']}}
                        </div>
                    </div>
                    {{-- Only type 2 --}}
                    @elseif($body[$i]['type_id']==config('TWO',2))
                    <div class="heading">
                        <div class="font-color-layout3 border-bottom">
                            <b>{{$body[$i]['heading_name']}}</b>
                        </div>
                        @if(!empty($body[$i]['value']))
                        <div class="subheading" style="padding: 5px; margin: 5px;">
                            <ul>
                                @foreach($body[$i]['value'] as $r)
                                @if(!empty($r['check']) and isset($r['level_name']))
                                <li><span>{{$r['subName']}}</span> &nbsp;(<span class='light'>{{$r['level_name']}}</span>)</li>
                                @elseif(!empty($r['check']) and !isset($r['level_name']))
                                <li><span>{{$r['subName']}}</span></li>
                                @endif
                                @endforeach

                            </ul>
                        </div>
                        @endif
                    </div>

                    @elseif($body[$i]['type_id']==config("SEVEN"))
                    <div class="heading">
                        <div class="font-color-layout3 border-bottom">
                            <b>{{$body[$i]['heading_name']}}</b>
                        </div>
                        <div class="subheading" style="padding: 5px; margin: 5px;">
                            <ul>
                                @foreach($body[$i]['value'] as $r)
                                <li>{{explode('/',$r)[count(explode('/',$r))-1]}}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                    @endif
                    @endfor
            </div>


        </div>
        <div class="right-col writable-area overflow-warp">
            @for($i=2; $i<count($body);$i++) @if(count($body)>$i)
                @if($body[$i]['type_id']!=config('TWO',2) and $body[$i]['type_id']!=config("SEVEN"))
                <div class=" heading">
                    <div class="font-color-layout3 border-bottom">
                        <b>{{$body[$i]['heading_name']}}</b>
                    </div>
                    <div class="subheading" style="padding: 10px;margin: 7px;">
                        {{$body[$i]['value']}}
                    </div>
                </div>

                @elseif($body[$i]['type_id']==config('TWO',2))
                <div class="heading">
                    <div class="font-color-layout3 border-bottom">
                        <b>{{$body[$i]['heading_name']}}</b>
                    </div>
                    @if(!empty($body[$i]['value']))
                    <div class="subheading" style="padding-left: 5px;margin-left: 5px;">
                        <ul>
                            @foreach($body[$i]['value'] as $r)
                            @if(!empty($r['check']) and isset($r['level_name']))
                            <li><span>{{$r['subName']}}</span> &nbsp;(<span class='light'>{{$r['level_name']}}</span>)</li>
                            @elseif(!empty($r['check']) and !isset($r['level_name']))
                            <li><span>{{$r['subName']}}</span></li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>


                @elseif($body[$i]['type_id']==config("SEVEN"))
                <div class="heading">
                    <div class="font-color-layout3 border-bottom">
                        <b>{{$body[$i]['heading_name']}}</b>
                    </div>
                    <div class="subheading" style="padding: 10px 5px 10px;margin-left: 5px;">
                        <ul>
                            @foreach($body[$i]['value'] as $r)
                            <li style="padding-bottom: 5px;">{{explode('/',$r)[count(explode('/',$r))-1]}}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
                @endif
                @endfor
        </div>
        </div>
    </main>
    <footer>

    </footer>
    <htmlpagefooter name="MyFooter1">

        <table width="100%">
            <tr>
                <td width="35%"></td>
                <td width="55%" style="text-align: right; height:50px;" class="layout3"></td>
            </tr>
        </table>

    </htmlpagefooter>
    <sethtmlpageheader name="MyHeader1" value="on" />
    <sethtmlpagefooter name="MyFooter1" value="on" />

</body>

</html>
