<div class="wrap">
    <style>
        .aliyun-progress {
            height: 30px;
            position: absolute;
            top: 0;
            left: 0;
        }

        .aliyun-progress-bg {
            background: #eee;
            width: 600px;
        }

        .aliyun-progress-fr {
            background: #00a2ca;
            width: 300px;
        }
    </style>
    <?php include plugin_dir_path(__FILE__) . '/header.php' ?>
    <dl class="ali-opensearch-panel clearfix">
        <dt class="ali-opensearch-panel-header">
            <span>导入所有文章</span>
        </dt>
        <dd>
            <div style=" height:200px;position: relative;" class="clearfix">
                <div class="aliyun-progress aliyun-progress-bg">
                </div>
                <div class="aliyun-progress aliyun-progress-fr" id="J_AliyunProcessFrontend"
                     style="width:<?php echo round($currentProcessing / $query->found_posts, 2) * 600 ?>px;">
                </div>
                <div style="font-size:24px; font-weight:bold; color:white;position: absolute;top:5px;left:10px;">
                    <?php echo $currentProcessing . '/' . $query->found_posts; ?>
                </div>

                <div style="position: absolute;top:100px;">
                    <?php if ($hasMore): ?>
                        正在导入您所有的文章和页面到阿里云,期间请勿添加新的文章或页面.也不要手动刷新页面.&nbsp;&nbsp;&nbsp;&nbsp;
                        <a href="javascript:aosStopJump();" id="J_StopIndex">停止导入</a>
                    <?php else: ?>
                        导入完毕!
                    <?php endif; ?>
                </div>
                <div class="clearfix"></div>
        </dd>
    </dl>
    <div class="clearfix"></div>
</div>
<script>
    var aosHasMorePosts = <?php var_export($hasMore);?>;
    var jumpTimeout = null;

    function doJump() {
        jQuery('#J_StopIndex').hide();
        document.location.href = "tools.php?page=aliyun-open-search-reindex&paged=<?php echo ++$paged; ?>";
    }
    function aosJumpNext() {
        jumpTimeout = setTimeout(doJump, 2000);
    }
    function aosStopJump() {
        if (jumpTimeout != null) {
            clearTimeout(jumpTimeout);
        }
        jQuery('#J_StopIndex').before('<span style="color:red;">已停止导入</span>');
        jQuery('#J_StopIndex').after('&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:doJump();">继续导入</a>');
        jQuery('#J_StopIndex').hide();
    }

    if (aosHasMorePosts) {
        aosJumpNext();
    }
</script>
<!--<script>-->
<!--    $(function() {-->
<!--        $('#J_AliyunProcessFrontend')-->
<!--    });-->
<!--</script>-->
