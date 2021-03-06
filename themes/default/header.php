<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="utf-8" />
    <title><?php echo $this->config['sitename'];?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords" content="<?php echo $this->config['keywords'];?>">
    <meta name="description" content="<?php echo $this->config['description'];?>">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.cookie@1.4.1/jquery.cookie.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
    <style>
        #send-box {
            min-height: 12rem;
            border: 1px solid #dee2e6;
            padding: 2rem
        }

        .text p {
            text-indent: 2em;
        }

        .htmlpage h1 {
            text-align: center;
            margin: 40px 0;
        }

        .sidebar {
            overflow: hidden;
            padding: 0 2rem;
            position: absolute;
        }


        .sidebar-left-nav {
            /* margin-top: 3rem; */
        }

        .sidebar-left-nav ul,
        .sidebar-left-nav li {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-left-nav li {
            height: 30px;
            display: flex;
            align-items: center;
        }

        .sidebar-left-nav li a {
            font-size: 14px;
            color: #333333;
        }


        .sidebar-left-title {
            color: #a5a5a4;
            margin-top: 20px;
        }

    </style>

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="/">裂变时代</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="?a=page&page_url=about">网盘介绍</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?a=page&page_url=about">广告投放</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?a=page&page_url=about">合作分成</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="?a=page&page_url=about" id="navbarDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        关于我们
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="?a=page&page_url=about">使用帮助</a>
                        <a class="dropdown-item" href="?a=page&page_url=about">关于我们</a>
                        <a class="dropdown-item" href="?a=page&page_url=about">加入我们</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="?a=page&page_url=about">隐私协议</a>
                    </div>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <?php if(isset($_SESSION['is_login']) && $_SESSION['is_login']){ ?>
                <li class="nav-item">
                    <a class="nav-link" href="?a=user&c=index">
                        <?php echo $_SESSION['user']['username'] ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?a=user&c=logout">注销</a>
                </li>
                <?php }else{ ?>
                <li class="nav-item">
                    <a class="nav-link" href="?a=login">登录</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="?a=register">注册</a>
                </li>
                <?php } ?>
            </ul>
        </div>
    </nav>