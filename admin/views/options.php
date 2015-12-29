<div class="wrap">

    <?php include dirname(__FILE__) . '/header.php' ?>


    <form method="post" action="options.php" novalidate="novalidate">
        <?php settings_fields(ALI_OPENSEARCH_PLUGIN_NAME); ?>
        <dl class="ali-opensearch-panel">
            <dt class="ali-opensearch-panel-header">
                <span>管理</span>
            </dt>
            <dd>
                <div style="padding:20px 0;" class="clearfix">
                    <a href="tools.php?page=aliyun-open-search-reindex" class="ali-button-active"
                       title="第一次也可以通过「重建索引」来初始化">重建索引</a>
                </div>
            </dd>
        </dl>
        <dl class="ali-opensearch-panel">
            <dt class="ali-opensearch-panel-header">
                <span>配置</span>
            </dt>
            <dd>
                <div class="alert alert-warning margin-top-1">
                    <span>
                        <i class="dashicons dashicons-warning"></i>
                        &nbsp;&nbsp;Access Key ID 和 Access Key Secret 是您访问阿里云API的密钥，具有该账户完全的权限，请您妥善保管。本插件由阿里云官方提供，请放心使用。
                    </span>
                </div>
                <div class="ali-form-control">
                    <label for="aliyun-access-key">Access Key ID: </label>
                    <input class="ali-opensearch-input" id="aliyun-access-key"
                           name="<?php echo AliyunOpenSearchOptions::OPTION_NAME_ACCESS_KEY; ?>"
                           value="<?php echo AliyunOpenSearchOptions::getAccessKey(); ?>">
                    <span class="ali-form-tip">
                        Access Key ID 和 Access Key Secret 可以从
                        <a href="https://ak-console.aliyun.com/#/accesskey" target="_blank">阿里云面板</a> 获得
                    </span>
                </div>
                <div class="ali-form-control">
                    <label for="aliyun-access-secret">Access Key Secret: </label>
                    <input class="ali-opensearch-input" id="aliyun-access-secret"
                           name="<?php echo AliyunOpenSearchOptions::OPTION_NAME_ACCESS_SECRET; ?>"
                           value="<?php echo AliyunOpenSearchOptions::getSecret(); ?>">
                </div>

                <div class="ali-form-control">
                    <label for="aliyun-app-name">应用名称: </label>
                    <input class="ali-opensearch-input" id="aliyun-app-name"
                           name="<?php echo AliyunOpenSearchOptions::OPTION_NAME_APP_NAME; ?>"
                           value="<?php echo AliyunOpenSearchOptions::getAppName(); ?>">
                    <span class="ali-form-tip">

                        先<a href="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . 'download-template.php' ?>">下载模板文件</a>
                        ，在阿里云后台<a href="http://opensearch.console.aliyun.com/console/#!/templates/create">创建模板</a>，
                        然后使用该模板<a href="http://opensearch.console.aliyun.com/console/#!/apps/create">创建应用</a>。
                    </span>

                </div>
                <div class="ali-form-control">
                    <label for="aliyun-api-host">API 地址: </label>
                    <input class="ali-opensearch-input" id="aliyun-api-host"
                           name="<?php echo AliyunOpenSearchOptions::OPTION_NAME_HOST; ?>"
                           value="<?php echo AliyunOpenSearchOptions::getHost(); ?>">


                    <a href="javascript:;" class="ali-button-active J_Ali-opensearch-host-helper"
                       data-host="http://opensearch-cn-hangzhou.aliyuncs.com">杭州</a>
                    <a href="javascript:;" class="ali-button-active J_Ali-opensearch-host-helper"
                       data-host="http://opensearch-cn-beijing.aliyuncs.com">北京</a>
                    <a href="javascript:;" class="ali-button-active J_Ali-opensearch-host-helper"
                       data-host="http://opensearch-cn-qingdao.aliyuncs.com">青岛</a>
                    <span class="ali-form-tip">
                       如您博客部署在阿里云，建议选择同区域的 API 地址，也可以直接使用创建应用后阿里云提供的地址。如 WordPress 部署在同区域的 ECS 或 ACE 中，可以使用内网地址以加快 API 的速度。API 地址必须使用创建应用时选择的区域对应的地址，不可以跨区域使用。
                    </span>
                </div>
                <div class="ali-form-control">
                    <label>&nbsp;</label>

                    <input type="submit" name="submit" id="submit" class="button button-primary"
                           value="保存更改">
                </div>

            </dd>
        </dl>

    </form>

</div>
<script>
    jQuery('.J_Ali-opensearch-host-helper').click(function () {
        jQuery('#aliyun-api-host').val(jQuery(this).data('host'));
    });
</script>