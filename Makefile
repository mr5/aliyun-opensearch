list:
	@echo "archive: Make archive for release under travis ci."
	@echo "travis-version-replacement: Just version replacement under travis ci."

travis-archive:
	-rm build/aliyun-opensearch-wordpress.zip
	zip -r build/aliyun-opensearch-wordpress.zip ./  -x "/build/*" -x "/tests/*" -x "/vendor/*" -x "/.git/*" -x "/.*"

travis-version-replacement:
	echo $TRAVIS_TAG > VERSION
	sed -i "s/Version: dev/Version: `cat VERSION`/g" ./aliyun-opensearch.php
	sed -i "s/Stable tag: dev/Stable tag: `cat VERSION`/g" ./readme.txt
