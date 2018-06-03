<!DOCTYPE html>
<html lang="en">
  <head>
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
      <script type="text/javascript" src="{{ asset("js/jquery.js") }}"></script>
      <meta name="api-token" content="{{ api_token() }}">
      <!-- CSRF Token -->
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <!--<script src="./src/assets/js/bootstrap.js"></script>-->
      <!--<style src="./src/assets/css/bootstrap.css"></style>-->
      <!--<style src="./src/assets/css/logo.css"></style>-->
      <link href="//cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <meta charset="utf-8">
    <title>my-webpack-simple-demo</title>
  </head>
  <body>
    <div id="app"></div>
    <script src="{{ asset('js/mobile/build.js') }}"></script>
  </body>
</html>
