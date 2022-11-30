<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body {
            font-family: 'zawgyi', times;
        }

        * {
            margin: 0;
            padding: 0;
        }

        .heading {
            width: 100%;
            font-size: x-large;
            font-family: 'zawgyi', 'Times New Roman', times;
        }

        .subheading {
            margin-top: 0.15em;
            margin-bottom: 0.15em;
            padding-top: 0.15em;
            padding-bottom: 0.15em;
            font-size: large;
            font-family: 'zawgyi', 'Helvetica';
        }

        .box {
            margin: 1em;
            padding: 0.75em;
            width: 30%;
        }

        .zawgyi {
            font-family: 'zawgyi';
        }

        .image-container {

            width: 150px;
            float: left;
        }

        .name-tag {
            width: 30%;
            padding: 0.5rem 1rem;
            float: left;
            color: white;
            font-weight: bold;
            font-size: 20pt;
            overflow-wrap: break-word;
            margin: 1rem;
            text-align: center;
            vertical-align: middle;
            font-family: 'zawgyi';
            margin-left: -10px;
        }

        .right-side {
            float: right;
            margin-top: -30px;
        }

        .font-color-layout3 {
            color: #8993EF;
        }

        .layout-one-header {
            background-color: #5AB4AB;
            height: 15%;
            padding: 2rem 3em;
        }

        .header-label-text {
            color: white;
            font-weight: bold;
            font-size: 16pt;
            overflow-wrap: break-word;
            font-family: 'zawgyi';
            margin-left: 100px;
            margin-top: -20px;

        }

        .bordered-text {
            border-radius: 3px;
            overflow-wrap: break-word;
            font-size: 14pt;
            color: white !important;
            overflow-wrap: break-word;
            margin-left: 100px;
            padding-top: -20px;
        }

        .sub-heading {

            margin-left: 50px;
            padding: 0.5rem 0 0.5rem;
            font-size: large;
            overflow-wrap: break-word;
        }

        img {
            border-radius: 5px;
            border: 1px solid grey;
        }
        body{
            font-family:'zawgyi';
        }
        @page{
            page-break-before: avoid;
        }
        .sub-heading-child {
            margin-left: 30px;
            padding: 0.3rem 0 0 0;
            font-size: small;
            overflow-wrap: break-word;
        }
        .heading1{
            font-size:16pt;
            padding:0.5em;
            line-height:normal;
            font-family:'zawgyi';
            word-break: break-word;
        }
        .border-bottom{
            color:#1d7afb;
            border-bottom: 1px solid #1d7afb;
        }
        .sub-heading1{
            font-size: 12pt;
            margin:0.5em 0;
            font-family:'zawgyi';
            word-break: break-word;
        }
        .wirtable-area1{
            margin: 0 1.75em;
            padding:0.25em 1.75em;
            line-height: normal;
        }
        .margin1{
            margin-left:1em;
            padding:0em 1em;
        }
        .bold{
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="layout-one-header">
        <div class="image-container" style= "margin-top:20px">
            @empty($image)
            <img src="{{public_path().'/img/profile.png'}}" style="background-color: aliceblue;
            " width="200" height="200"

            />
            @else
            <img src="{{storage_path().'/app/public/'.$image}}" style="background-color: aliceblue; "
            width="200" height="200" />
            @endempty

        </div>

        <p class="name-tag zawgyi">
        @isset($name_dict['name'])
            @isset($name_dict['name']['value'])
                @if(is_array($name_dict['name']['value']))

                    @foreach($name_dict['name']['value'] as $k)
                            @if($name_dict['name']['type_id']!= config("TWO"))
                                <!-- <div style="word-break:break-all;white-space: normal;color:white;width:30%;">{{$k}}</div> -->
                                <span style="word-break:break-all;white-space: normal;color:white;width:30%;padding: 0.5rem 1rem;font-weight: bold;">{{$k}}</span>
                            @else
                                    @if(!empty($k['check']) and isset($k['level_name']))
                                    <span style="word-break:break-all;white-space: normal;color:white;width:30%;padding: 0.5rem 1rem;font-weight: bold;">{{$k['subName']}} &nbsp;({{$k['level_name']}})</span>
                                    @elseif(!empty($k['check']) and !isset($k['level_name']))
                                    <span style="word-break:break-all;white-space: normal;color:white;width:30%;padding: 0.5rem 1rem;font-weight: bold;">{{$k['subName']}}</span>
                                    @endif
                            @endif
                    @endforeach

                @else
                    <span style="word-break:break-all;white-space: normal;color:white;width:30%;padding: 0.5rem 1rem;font-weight: bold;">{{$name_dict['name']['value']}}</span>
                @endif
            @endisset
            @endisset
        </p>


        <div class="right-side">
            @foreach($head as $k)
            <p class="header-label-text">
                {{$k['heading_name']}} :
            </p>
            @if(!empty($k['value']))
            <p class="bordered-text">
                @if(is_array($k['value']))
                    @foreach($k['value'] as $s)
                    @if($k['type_id']!= config("TWO"))
                        <span style="word-break:break-all;white-space: normal;color:white;">{{$s}}<br /></span>
                    @else
                            @if(!empty($s['check']) and isset($s['level_name']))
                                <span style="word-break:break-all;white-space: normal;color:white;">{{$s['subName']}} &nbsp;({{$s['level_name']}})<br /></span>
                            @elseif(!empty($s['check']) and !isset($s['level_name']))
                                <span style="word-break:break-all;white-space: normal;color:white;">{{$s['subName']}}<br /></span>
                            @endif
                    @endif
                    @endforeach
                @else
                <span style="word-break:break-all;white-space: normal;color:white;">{{$k['value']}}</span>
                @endif
            </p>
            @endif
            @endforeach
        </div>
    </div>
    <div class="wirtable-area1">
        @foreach($body as $j)
        @if($j['type_id']<config("SEVEN") and $j['type_id']!=config("TWO"))
        <div class="heading1">
            <div class="bold border-bottom">
            {{$j['heading_name']}}
            </div>
            @if(!empty($j['value']))
            <div class="sub-heading1">
                {{$j['value']}}
            </div>
            @endif
        </div>
        @elseif($j['type_id']==config("TWO"))
        <div class="heading1">
            <div class='bold border-bottom'>
                {{$j['heading_name']}}

            </div>

            @if(!empty($j['value']))
            <div class="sub-heading1 bold ">
            <ul class="margin1">
            @foreach($j['value'] as $data)
            @if(!empty($data['check']) and !empty($data['level_name']))
                    <li>{{$data['subName']}}({{$data['level_name']}})</li>
            @elseif(!empty($data['check']) and empty($data['level_name']))
                    <li>{{$data['subName']}}</li>
            @endif
            @endforeach
            </ul>
            </div>
            @endif
        </div>
        @elseif($j['type_id']==config("SEVEN"))
        <div class="heading1">
            <div class="bold border-bottom">
            {{$j['heading_name']}}

            </div>
            @if(!empty($j['value']))
            <div class="sub-heading1  bold">
            <ul class="margin1">
            @foreach($j['value'] as $data)
                    <li>{{explode('/',$data)[count(explode('/',$data))-1]}}</li>
            @endforeach
            </ul>
            </div>
            @endif
        </div>
        @endif
        @endforeach
    </div>
</body>

</html>
