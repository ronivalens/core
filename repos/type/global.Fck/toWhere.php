<? return "_no_accents (". $field->getTable () .'.'. $field->getColumn () .") ILIKE _no_accents ('%". addslashes (htmlentities ($field->getValue (), ENT_QUOTES, 'UTF-8')) ."%')" ?>