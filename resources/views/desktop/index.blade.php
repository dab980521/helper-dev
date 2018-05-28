<?php
/**
 * Created by PhpStorm.
 * User: johnd
 * Date: 2018/5/17
 * Time: 上午11:46
 */ ?>

        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>webpack-simple-admain</title>
    <meta name="api-token" content="{{ api_token() }}">
    {{--<script src="{{ asset('js/jquery.js') }}"></script><!--jQuery 3.3.1-->--}}
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/desktop/simditor.css') }}" />
</head>
<body>
<div id="app"></div>
<script src="//localhost:8080/dist/build.js"></script>
<script type="text/javascript" src="{{ asset('js/jquery.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/editor/module.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/editor/hotkeys.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/editor/uploader.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/editor/simditor.js') }}"></script>
</body>
</html>

