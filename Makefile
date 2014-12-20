clean: 
	find ./var/cache/ -type f -exec rm -f {} \;   ; find ./var/full_page_cache -type f -exec rm -f {} \;
