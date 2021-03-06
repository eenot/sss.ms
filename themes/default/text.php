<?php include(TEMPLATE . "/header.php") ?>
<!-- include summernote css/js -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<!-- 页面主要内容 start -->
<div class="container">
    <h1 style="text-align: center;margin: 100px 0;"><?php echo $this->config['sitename'];?></h1>
    <div class="col-xs-12 col-sm-12 col-md-8 center-block" style="margin: 40px auto;">
        <ul class="nav nav-tabs" style="border-bottom: 0;">
            <li class="nav-item">
                <a class="nav-link" href="/">文件发送</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="?a=text">文字文本</a>
            </li>
            <!-- <li class="nav-item">
                <a class="nav-link " href="?a=encryption">超级加密</a>
            </li> -->
            <li class="nav-item">
                <a class="nav-link" href="?a=receive">文件接收</a>
            </li>
        </ul>
        <div id="send-box">
            <form  action="?a=upload&c=text" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <textarea id="summernote" name="text"></textarea>
                </div>
                <div class="form-check form-check-inline">
                    <label class="form-check-label" for="time" style="width: 10rem">文件有效期</label> 
                    <select id="time" name="time" class="form-control">
                        <option value="1" selected>阅后即焚</option>
                        <option value="1d">一天</option>
                        <option value="7d">一周</option>
                        <option value="forever">永久 (需登录)</option>
                    </select>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="isLAN" name="isLAN" value="1" checked>
                    <label class="form-check-label" for="isLAN">内网共享</label>
                </div>
                <button class="btn btn-warning" style="float: right;" type="submit">保存&发送</button>
                
            </form>

        </div>

        <?php  if(isset($intranet) && $intranet){ ?>
            <div class="mt-3">
                <div class="card">
                    <div class="card-header">
                        内网共享文本
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php  foreach ($intranet as &$item){  ?>
                            <li class="list-group-item">
                                <a href="<?php echo url('text_share',$item['alias']);?>">
                                    创建于<?php echo zpl_time($item['create_time']); ?>
                                    
                                    -- 
                                    <?php 
                                        if($item['is_only']){
                                           echo '阅后即焚';
                                        }else if($item['expire_time']){
                                            echo date("Y-m-d H:i",$item['expire_time']).'后过期';
                                        }else{
                                            echo '永久有效';
                                        }
                                    ?>
                                </a> 
                            </li>
                        <?php }  ?>
                    </ul>
                </div>
            </div>
        <?php  }  ?>


    </div>
</div>
<!-- 页面主要内容 end -->
<script>
     $('#summernote').summernote({
        placeholder: '请在这里输入，如果内容包含js代码会被系统自动清除',
        tabsize: 2,
        height: 300
      });
  </script>
<?php include(TEMPLATE . "/footer.php") ?>