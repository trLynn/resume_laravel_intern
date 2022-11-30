<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Email Verification</title>
</head>
<body>
  <h3>Hello {{$mailData['title']}}</h3>
  <p>We received your request for a single-use code to use with your Mail account.</p>
  <hr>
  <p>Your verification code is: <span style="font-weight:bold">{{$mailData['body_message']}}</span></p>

  <hr>
  <p>Please do not share this <span style="color: red">OTP</span> with anyone.</p>
  <p><i>Thank you,</i></p>
  <p><i>BrycenMyanmar Team</i></p>
</body>
</html>
