<?php

return array(
        
	// Whether to normalize tags at all (recommended, as raw tags are preserved anyway.)
	'normalize_tags'          => FALSE,

	// Use Kohana's URL::title() helper for normalization
	'use_gleez_normalization' => FALSE,

	// If 'use_gleez_normalization' is set to FALSE, you can define your own normalization here.
	'custom_normalization'    => '-a-zA-Z0-9',

	// Will append this string to any integer tags. This is supposed to prevent PHP
	// casting "string" integer tags as ints. Won't do anything to floats or non-numeric strings.
	'append_to_integer'       => '',

	// The maximum length of a tag.
	'max_tag_length'          => 32,

	// The minimum length of a tag.
	'min_tag_length'          => 3,

	// The tags table name, tags are stored. Must be plural to properly handle joins.
	'tag_table'               => 'tags',

	// The taggings model name, where taggings (when an object is tagged) are stored.
	//Must be plural to properly handle joins.
	'tagging_model'           => 'tagging',

	// The taggings table name, where taggings (when an object is tagged) are stored.
	//Must be plural to properly handle joins.
	'tagging_table'           => 'posts_tags',

	// The foreign key name of the object being tagged (ie. blog_id, comment_id, etc.)
	'object_foreign_key'      => 'post_id',

);