<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        .left-col {
            float: left;
            padding: 1em;
            width: 30%;
        }

        .right-col {
            float: left;
            padding: 1em;
            width: 59%;

        }

        body,
        html {
            height: 100%;
        }

        h-100 {
            height: 100%;
        }


        .layout2 {
            background-color: #5D8389;
        }

        .image {
            border-radius: 50%;
            margin: 5em 3em;
            display: block;
            border-collapse: separate;
            overflow: hidden;
            padding :4em;
        }

        .name {
            text-align: center;
            font-size: 1.5rem;
        }

        .heading {
            margin: 0.5em;
            padding: 0.25em;
        }

        .subheading {
            margin: 0.5em;
            padding: 0.25em;
        }


        .w-50 {
            width: 50%;
        }

        .foot {
            position: absolute;
            bottom: 0;
        }

        .center {
            text-align: center;
        }

        .layout3 {
            background-color: #8993EF;
        }

        .w-100 {
            width: 100%;
        }

        .font-color-layout3 {
            color: #8993EF;
        }

        .profile_img {
            width: 120px;
            height: 120px;
            border-radius: 120px;
            border-style: solid;
            border-color: white;
            border-width: medium;

            overflow: hidden;

            background-size: 150px 150px;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
        }
    </style>
</head>

<body>
    <header style="position:fixed; width:100%;">
        <div class="block left-col layout3"></div>
        <div class="right-col block"></div>
    </header>

    <div class="left-col font-color-layout3 center">
        @empty($image)
        <img src="{{public_path().'/img/profile.jpg'}}" style="background-color: aliceblue;" width="90" height="90" />
        @else
            <img src="{{storage_path().'/app/public/'.$image}}" class='image' width="90" height="90" />
        @endempty
        <div class="name">
        @isset($head['name'])
            {{$head['name']['value']}}
        @endisset
        </div>
        <div class="heading">
            @if(!empty($head))
            Contact
            <div class='subheading'>
                @isset($head['address'])
                <div>
                    {{$head['address']['value']}}
                </div>
                @endisset
                @isset($head['email'])
                <div>
                    {{$head['email']['value']}}
                </div>
                @endisset
                @isset($head['phonenumber'])
                <div>
                    {{$head['phonenumber']['value']}}
                </div>
                @endisset
            </div>
            @endif
        </div>

        @for($i=0; $i<2;$i++)
        @if(count($body)>$i)
        @if($body[$i]['type_id']!=config('TWO',2))
        <div class="heading">
            <div>
                {{$body[$i]['heading_name']}}
            </div>
            <div class="subheading">
                {{$body[$i]['value']}}
            </div>
        </div>

        @elseif($body[$i]['type_id']==config('TWO',2))
        <div class="heading">
            <div>
                {{$body[$i]['heading_name']}}
            </div>
            <div class="subheading">
                <ul>
                    @foreach($body[$i]['value'] as $r)
                    <li>{{$body[$i]['value']}}({{$body[$i]['level_name']}})</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
        @endif
        @endfor
    </div>
    <div class="right-col font-color-layout3">
    @for($i=2; $i<count($body);$i++)
        @if($body[$i]['type_id']!=config('TWO',2))
        <div class="heading">
            <div>
                {{$body[$i]['heading_name']}}
            </div>
            <div class="subheading">
                {{$body[$i]['value']}}
            </div>
        </div>

        @elseif($body[$i]['type_id']==config('TWO',2))
        <div class="heading">
            <div>
                {{$body[$i]['heading_name']}}
            </div>
            <div class="subheading">
                <ul>
                    @foreach($body[$i]['value'] as $r)
                    <li>{{$r['subName']}}({{$r['level_name']}})</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
        @endforeach
    </div>
    <div>
        @foreach($footer as $body[$i]['value'])
        <div class="heading left-col font-color-layout3 w-100">
            $body[$i]['heading_name']
            <div class="subheading">
                $body[$i]['value']
            </div>
        </div>
        </div>
    @endforeach
    <footer class='footer'>
        <div class="block left-col "></div>
        <div class="block" style="background-color: #8993EF; float:left; width:59%;"></div>
    </footer>

</body>

</html>
