<div class="wrap">
    <?php include dirname(__FILE__) . '/header.php' ?>
    <dl class="ali-opensearch-panel clearfix">
        <dt class="ali-opensearch-panel-header">
            <span>导入所有文章</span>
        </dt>
        <dd>
            <div style=" height:200px;position: relative;" class="clearfix">
                <div class="aliyun-progress aliyun-progress-bg">
                </div>
                <div class="aliyun-progress aliyun-progress-fr" id="J_AliyunProcessFrontend"
                     style="width:<?php echo $query->found_posts > 0 ? round($currentProcessing / $query->found_posts,
                             2) * 600 : 0 ?>px;">
                </div>
                <div style="font-size:24px; font-weight:bold; color:white;position: absolute;top:5px;left:10px;">
                    <?php echo $currentProcessing . '/' . $query->found_posts; ?>
                </div>

                <div style="position: absolute;top:100px;">

                    <?php if ($hasMore): ?>
                        正在导入您所有的文章和页面到阿里云,期间请勿添加新的文章或页面.也不要手动刷新页面.&nbsp;&nbsp;&nbsp;&nbsp;
                        <a href="javascript:aosStopJump();" id="J_StopIndex">停止导入</a>

                    <?php else: ?>
                        <?php if ($paged == 0): ?>
                            本工具可以将您所有的文章导入到阿里云,期间请不要对文章或页面做任何操作.
                            <br>
                            <a href="tools.php?page=aliyun-open-search-reindex&paged=1">开始导入</a>
                        <?php else: ?>
                            导入完毕!
                        <?php endif; ?>
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
