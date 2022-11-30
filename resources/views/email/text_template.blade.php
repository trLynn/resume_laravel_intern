<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail</title>
</head>
<body>
    <div>
        @isset($mailData['title'])
            <div style="background-color:#4d9096;color:#fff;"><h2>{{ $mailData['title'] }}</h2></div><!-- title -->
        @endisset
        <p>Dear Mr/Ms,</p>
        @isset($mailData['body_message'])
            <p style="color:#67a0a5">{!! $mailData['body_message'] !!}</p><!-- body message -->
        @endisset
        @isset($mailData['link']) <!-- message link -->
            <p style="color:#0000FF">
                @isset($mailData['link_message'])
                    {!! $mailData['link_message'] !!}
                @endisset
                <a href="{{ url($mailData['link']) }}">{{ $mailData['link'] }}</a>
            </p>
        @endisset
        Regards,<br>
        Brycen Myanmar
    </div>
    <!-- <div style='padding-top:15px;'>
        <p>Thanks, <br>{{ env('APP_NAME') }}</p>
    </div> -->
</body>
</html>
