<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール</title>
</head>

<body>
    <h1>プロフィールページ</h1>
    <p>ようこそ、{{ Auth::user()->name }} さん！</p>
</body>

</html>