<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login Form</title>
</head>
<body>


    @if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    @endif

    <form action="/form" method="post">
        @csrf
        <label for="username">
            Username:
        </label>
        <input type="text" name="username" id="username" >
        <br>
        <label for="password">
            Password:
        </label>
        <input type="password" name="password" id="password" >
        <br>
        <input type="submit" value="Login">
    </form>

</body>
</html>
