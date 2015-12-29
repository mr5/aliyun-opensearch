list:
	@echo "archive: Make archive for release."
archive:
	-rm build/aliyun-opensearch-wordpress.zip
	zip -r build/aliyun-opensearch-wordpress.zip ./  -x "/build/*" -x "/tests/*" -x "/vendor/*" -x "/.git/*" -x "/.*"