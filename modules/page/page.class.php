<?php
    /**
     * @class  page
     * @author NHN (developers@xpressengine.com)
     * @brief high class of the module page
     **/

    class page extends ModuleObject {

        /**
         * @brief Implement if additional tasks are necessary when installing
         **/
        function moduleInstall() {
            // page generated from the cache directory to use
            FileHandler::makeDir('./files/cache/page');

            return new Object();
        }

        /**
         * @brief a method to check if successfully installed
         **/
        function checkUpdate() {
			$output = executeQuery('page.pageTypeOpageCheck');
			if ($output->toBool() && $output->data) return true;

			$output = executeQuery('page.pageTypeNullCheck');
			if ($output->toBool() && $output->data) return true;

            return false;
        }

        /**
         * @brief Execute update
         **/
        function moduleUpdate() {
			// opage module instance update
			$output = executeQueryArray('page.pageTypeOpageCheck');
			if ($output->toBool() && count($output->data) > 0){
				foreach($output->data as $val){
					$args->module_srl = $val->module_srl;
					$args->name = 'page_type';
					$args->value= 'OUTSIDE';
					$in_out = executeQuery('page.insertPageType', $args);
				}
				$output = executeQuery('page.updateAllOpage');
			}

			// old page module instance update
			$output = executeQueryArray('page.pageTypeNullCheck');
			if ($output->toBool() && $output->data){
				foreach($output->data as $val){
					$args->module_srl = $val->module_srl;
					$args->name = 'page_type';
					$args->value= 'WIDGET';
					$in_out = executeQuery('page.insertPageType', $args);
				}
			}
            return new Object(0,'success_updated');
        }

        /**
         * @brief Re-generate the cache file
         **/
        function recompileCache() {
            // Delete the cache file pages
            FileHandler::removeFilesInDir("./files/cache/page");
        }
    }
?>
