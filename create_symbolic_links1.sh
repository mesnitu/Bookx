#!/bin/bash

windows() { [[ -n "$WINDIR" ]]; }

# version BookX 0.9.6 BETA
#src_dir=/FULL_PATH_TO_YOUR_BOOKX_INSTALLATION/bookX/ZC_INSTALLATION
#dst_dir=/FULL_PATH_TO_YOUR_ZENCART_INSTALLATION/zen-cart
#admin_dir_name=NAME_OF_YOUR_ADMIN_DIR
#tpl_dir_name=NAME_OF_YOUR_TEMPLATE

src_dir="c:\xampp\htdocs\vhosts\bookx-zc155f\ZC_INSTALLATION"
dst_dir="c:\xampp\htdocs\vhosts\zencart"
admin_dir_name="zenadmin"
tpl_dir_name="responsive_classic"

admin_path=${dst_dir}\\${admin_dir_name}

echo
echo "Source Directory: $src_dir"
echo "Destination Directory: $dst_dir"
echo "Admin Path: $admin_path"
echo "Template Directory Name: $tpl_dir_name"
echo

admin_files=(
    bookx_author_types.php 
    bookx_authors.php 
    bookx_binding.php
    bookx_conditions.php
    bookx_genres.php
    bookx_imprints.php
    bookx_printing.php
    bookx_publishers.php
    bookx_series.php
    bookx_tools.php
    #product_bookx.php
    
    # files in admin/includes
    includes/auto_loaders/config.product_type_bookx.php
    includes/classes/observers/class.bookx_admin_observers.php
    includes/extra_datafiles/bookx_type_database_names.php
    includes/extra_datafiles/bookx_type_filenames.php
    includes/extra_datafiles/bookx_sanitizer_fields.php
    includes/functions/extra_functions/product_bookx_functions.php
    includes/init_includes/init_product_type_bookx.php
    # files in admin/includes/modules/product_bookx
    includes/modules/product_bookx/collect_info_metatags.php
    includes/modules/product_bookx/collect_info.php
    includes/modules/product_bookx/copy_product_confirm.php
    includes/modules/product_bookx/delete_product_confirm.php
    includes/modules/product_bookx/preview_info.php
    includes/modules/product_bookx/preview_info_meta_tags.php
    includes/modules/product_bookx/update_product.php
    # files in admin/includes/languages/english
    includes/languages/english/bookx_author_types.php
    includes/languages/english/bookx_authors.php
    includes/languages/english/bookx_binding.php
    includes/languages/english/bookx_conditions.php
    includes/languages/english/bookx_genres.php
    includes/languages/english/bookx_imprints.php
    includes/languages/english/bookx_printing.php
    includes/languages/english/bookx_publishers.php
    includes/languages/english/bookx_series.php
    includes/languages/english/product_bookx.php
    includes/languages/english/extra_definitions/product_bookx.php
    # files in admin/includes/languages/german
    includes/languages/german/bookx_author_types.php
    includes/languages/german/bookx_authors.php
    includes/languages/german/bookx_binding.php
    includes/languages/german/bookx_conditions.php
    includes/languages/german/bookx_genres.php
    includes/languages/german/bookx_imprints.php
    includes/languages/german/bookx_printing.php
    includes/languages/german/bookx_publishers.php
    includes/languages/german/bookx_series.php
    includes/languages/german/product_bookx.php
    includes/languages/german/extra_definitions/product_bookx.php
    #since v1.0.0
    includes/extra_configures/bookx_extrafiles_folder.php
    includes/extra_datafiles/bookx/installers/bookx_install_v1.php
    includes/extra_datafiles/bookx/installers/bookx_update_v09.php
    includes/extra_datafiles/bookx/installers/bookx_update_v091.php
    includes/extra_datafiles/bookx/installers/bookx_update_v092.php
    includes/extra_datafiles/bookx/installers/bookx_update_v093.php
    includes/extra_datafiles/bookx/installers/bookx_update_v094.php
    includes/extra_datafiles/bookx/installers/bookx_update_v095.php
	includes/extra_datafiles/bookx/libs/Parsedown.php
    includes/extra_datafiles/bookx/plugin_check.json
    includes/extra_datafiles/bookx/Documentation.md
	includes/extra_datafiles/bookx/libs/Parsedown.php
    includes/extra_datafiles/bookx/libs/prism.css
    includes/extra_datafiles/bookx/libs/prism.js
    includes/classes/bookx/BookxFamilies.php
	includes/classes/bookx/BookxDinamicMetaTags.php
    includes/languages/english/bookx_families.php
    bookx_families.php
    )

#files in catalog
catalog_files=(
    includes/auto_loaders/config.bookx.php
    includes/classes/observers/class.bookx_observers.php
    includes/extra_configures/bookx_defines_and_configures.php
    includes/extra_datafiles/bookx_type_database_names.php
    includes/functions/extra_functions/functions_product_type_bookx.php
    includes/index_filters/bookx_filter.php
    #files in includes/languages/english
    includes/languages/english/product_bookx_info.php
    includes/languages/english/extra_definitions/product_bookx.php
    #files in includes/languages/german
    includes/languages/german/product_bookx_info.php
    includes/languages/german/extra_definitions/product_bookx.php
    #files in includes/modules
    includes/modules/product_bookx_prev_next.php
    includes/modules/pages/bookx_authors_list/header_php.php
    includes/modules/pages/bookx_genres_list/header_php.php
    includes/modules/pages/bookx_imprints_list/header_php.php
    includes/modules/pages/bookx_publishers_list/header_php.php
    includes/modules/pages/bookx_series_list/header_php.php
    includes/modules/pages/product_bookx_info/header_php.php
    includes/modules/pages/product_bookx_info/jscript_main.php
    includes/modules/pages/product_bookx_info/jscript_textarea_counter.js
    includes/modules/pages/product_bookx_info/main_template_vars_product_type.php
    includes/modules/pages/product_bookx_info/main_template_vars.php
    includes/modules/sideboxes/bookx_filters.php
    #files in includes/templates
    includes/templates/template_default/sideboxes/tpl_bookx_filters_select.php
    includes/templates/template_default/templates/tpl_bookx_authors_list_default.php
    includes/templates/template_default/templates/tpl_bookx_genres_list_default.php
    includes/templates/template_default/templates/tpl_bookx_imprints_list_default.php
    includes/templates/template_default/templates/tpl_bookx_publishers_list_default.php
    includes/templates/template_default/templates/tpl_bookx_series_list_default.php
    includes/templates/template_default/templates/tpl_product_bookx_info_display.php
    includes/templates/template_default/templates/tpl_bookx_products_next_previous.php
)

echo "== Options ==============================="
echo "-> Create symlinks -------------- [create]"
echo "-> Delete symlinks -------------- [delete]"
echo "-> Copy Files (for install) ----- [copy]"

while true; do
    
    read -e -p "Option [type option]: " options
    
    if [ -z "$options" ]; then
	    echo -n ""
        #echo $options
    else
        if [ "$options" == "create" ]; then
            echo "-> Create symlinks: $options"        
        fi
	    if [ "$options" == "delete" ]; then
            echo "-> Delete symlinks: $options"
        fi
		if [ "$options" == "copy" ]; then
            echo "-> Delete symlinks: $options"
        fi
        break;
    fi
done

if [ "$options" == "create" ]; then

    #create folders first
    umask 000
    mkdir -p ${dst_dir}/${admin_dir_name}/includes/classes/observers
    mkdir -p ${dst_dir}/${admin_dir_name}/includes/modules/product_bookx
    mkdir -p ${dst_dir}/${admin_dir_name}/includes/languages/german
    mkdir -p ${dst_dir}/${admin_dir_name}/includes/languages/german/extra_definitions
    
    # v1.0.0 Adds Installer and bookx folder to extra_datafiles
    mkdir -p ${dst_dir}/${admin_dir_name}/includes/extra_configures
    mkdir -p ${dst_dir}/${admin_dir_name}/includes/extra_datafiles/bookx
    mkdir -p ${dst_dir}/${admin_dir_name}/includes/extra_datafiles/bookx/installers
	mkdir -p ${dst_dir}/${admin_dir_name}/includes/extra_datafiles/bookx/libs
    # v1.0.0 Add classes/bookx folder
    mkdir -p ${dst_dir}/${admin_dir_name}/includes/classes/bookx
    mkdir -p ${dst_dir}/includes/languages/german
    mkdir -p ${dst_dir}/includes/languages/german/extra_definitions
    mkdir -p ${dst_dir}/includes/classes/observers
    mkdir -p ${dst_dir}/includes/modules/pages/bookx_authors_list
    mkdir -p ${dst_dir}/includes/modules/pages/bookx_genres_list
    mkdir -p ${dst_dir}/includes/modules/pages/bookx_imprints_list
    mkdir -p ${dst_dir}/includes/modules/pages/bookx_publishers_list
    mkdir -p ${dst_dir}/includes/modules/pages/bookx_series_list
    mkdir -p ${dst_dir}/includes/modules/pages/product_bookx_info
    mkdir -p ${dst_dir}/includes/templates/${tpl_dir_name}/css

    umask 022
    for i in "${admin_files[@]}"; do
        if windows; then
            #echo "$i"
            cmd <<< "mklink \"${admin_path}\\$i\" \"${src_dir}\\[RENAME_TO_YOUR_ADMIN_FOLDER]\\$i\" "
        else
            ln -s ${src_dir}/[RENAME_TO_YOUR_ADMIN_FOLDER]/"$i" ${admin_path}/"$i"
        fi
    done

    for i in "${catalog_files[@]}"; do
        if windows; then
            #echo "$i"
            cmd <<< "mklink \"${dst_dir}\\$i\" \"${src_dir}\\$i\" "
        else
            ln -s ${src_dir}/[RENAME_TO_YOUR_ADMIN_FOLDER]/"$i" ${admin_path}/"$i"
        fi
    done
    #link template file
    if windows; then
        echo -n | cmd <<< "mklink \"${dst_dir}\\includes\\templates\\${tpl_dir_name}\\css\\stylesheet_bookx.css\" \"${src_dir}\\includes\\templates\\[YOUR-TEMPLATE]\\css\\stylesheet_bookx.css\" "
    else
        ln -sf ${src_dir}/includes/templates/[YOUR-TEMPLATE]/css/stylesheet_bookx.css ${dst_dir}/includes/templates/${tpl_dir_name}/css/stylesheet_bookx.css     
    fi
elif [ "$options" == "delete" ]; then
    for i in "${admin_files[@]}"; do
        rm -v ${admin_path}/"$i"
        #echo "Delete admin files $i"
    done
    for i in "${catalog_files[@]}"; do
        rm -v ${dst_dir}/"$i"
        #echo "Delete catalog files ${dst_dir}/$i"
    done
    rm -v ${dst_dir}/includes/templates/${tpl_dir_name}/css/stylesheet_bookx.css
    #echo "Delete template files"
    echo "Done"
else 
	echo "not yet done copy files"
fi

read -e -p "Done! Review or Click to Exit" exit
if [ -z "$exit" ]; then
	cmd <<< "exit"
fi
