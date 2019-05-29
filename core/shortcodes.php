<?php
/**
 * This file contains the shortcode functions (without [tp_enrollments])
 * 
 * @package teachpress\core\shortcodes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * This class contains all shortcode helper functions
 * @since 5.0.0
 * @package teachpress\core\shortcodes
 */
class tp_shortcodes {
    
    /**
     * Returns a table headline for a course document list
     * @param array $row        An associative array of document data (i.e. name)
     * @param int $numbered     Display a numbered list (1) or not (0)
     * @param int $show_date    Display the upload date (1) or not (0)
     * @return string
     * @since 5.0.0
     */
    public static function get_coursedocs_headline ($row, $numbered, $show_date) {
        $span = 1;
        if ( $numbered === 1 ) {
            $span++;
        }
        if ( $show_date === 1 ) {
            $span++;
        }
        $colspan = ( $span > 1 ) ? 'colspan="' . $span . '"' : '';
        return '<th class="tp_coursedocs_headline" ' . $colspan . '>' . stripcslashes($row['name']) . '</th>';
    }
    
    /**
     * Returns a single table line for the function tp_courselist()
     * @param array $row            An associative array of document data (i.e. name, added)
     * @param array $upload_dir     An associative array of upload dir data
     * @param string $link_class    The link class
     * @param string $date_format   A typical date format string like d.m.Y
     * @param int $numbered         Display a numbered list (1) or not (0)
     * @param int $num              The current position in a numbered list
     * @param int $show_date        Display the upload date (1) or not (0)
     * @return string
     * @since 5.0.0
     */
    public static function get_coursedocs_line ($row, $upload_dir, $link_class, $date_format, $numbered, $num, $show_date) {
        $return = '';
        $date = date( $date_format, strtotime($row['added']) );
        if ( $numbered === 1 ) {
            $return .= '<td>' . $num . '</td>';
        }
        if ( $show_date === 1 ) {
            $return .= '<td><span title="' . __('Published on','teachpress') . ' ' . $date . '">' . $date . '</span></td>';
        }
        $return .= '<td><a href="' . $upload_dir['baseurl'] . $row['path'] . '" class="' . $link_class . '">' . stripcslashes($row['name']) . '</a></td>';
        return $return;
    }

    /**
     * Returns a single table line for the function tp_courselist()
     * @param object $row       The course object
     * @param string $image     The image position (left, right, bottom)
     * @param int image_size    The image size in px
     * @param string $sem       The semester you want to show
     * @return string
     * @since 5.0.0
     * @access public
     */
    public static function get_courselist_line ($row, $image, $image_size, $sem) {
        $row->name = stripslashes($row->name);
        $row->comment = stripslashes($row->comment);
        $childs = '';
        $div_cl_com = '';
        // handle images	
        $td_left = '';
        $td_right = '';
        if ( $image == 'left' || $image == 'right' ) {
            $pad_size = $image_size + 5;
        }
        $image_marginally = '';
        $image_bottom = '';
        if ( $image == 'left' || $image == 'right' ) {
            if ( $row->image_url != '' ) {
                $image_marginally = '<img name="' . $row->name . '" src="' . $row->image_url . '" width="' . $image_size .'" alt="' . $row->name . '" />';
           }
        }
        if ( $image == 'left' ) {
            $td_left = '<td width="' . $pad_size . '">' . $image_marginally . '</td>';
        }
        if ( $image == 'right' ) {
            $td_right = '<td width="' . $pad_size . '">' . $image_marginally . '</td>';
        }
        if ( $image == 'bottom' && $row->image_url != '' ) {
                $image_bottom = '<div class="tp_pub_image_bottom"><img name="' . $row->name . '" src="' . $row->image_url . '" style="max-width:' . $image_size .'px;" alt="' . $row->name . '" /></div>';
        }

        // handle childs
        if ( $row->visible == 2 ) {
            $div_cl_com = "_c";
            $row2 = tp_courses::get_courses( array('semester' => $sem, 'parent' => $row->course_id, 'visibility' => '1,2') );
            foreach ( $row2 as $row2 ) {
                $childs .= '<p><a href="' . get_permalink($row2->rel_page) . '" title="' . $row2->name . '">' . $row2->name . '</a></p>'; 
            }
            if ( $childs != '') {
                $childs = '<div class="tp_lvs_childs" style="padding-left:10px;">' . $childs . '</div>';
            }
        }

        // handle page link
        if ( $row->rel_page == 0 ) {
            $direct_to = '<strong>' . $row->name . '</strong>';
        }
        else {
            $direct_to = '<a href="' . get_permalink($row->rel_page) . '" title ="' . $row->name . '"><strong>' . $row->name . '</strong></a>';
        }
        
        $return = '<tr>
                   ' . $td_left . '
                   <td class="tp_lvs_container">
                       <div class="tp_lvs_name">' . $direct_to . '</div>
                       <div class="tp_lvs_comments' . $div_cl_com . '">' . nl2br($row->comment) . '</div>
                       ' . $childs . '
                       ' . $image_bottom . '
                   </td>
                   ' . $td_right . '  
                 </tr>';
        return $return;
    }
    
    /**
     * Returns html lines with course meta data. This function is used for tp_courseinfo_shortcode().
     * @param int $course_id        The course ID
     * @param array $fields         An associative array with informations about the meta data fields (variable, value)
     * @return string
     * @since 5.0.0
     */
    public static function get_coursemeta_line ($course_id, $fields) {
        $return = '';
        $course_meta = tp_courses::get_course_meta($course_id);
        foreach ($fields as $row) {
            $col_data = tp_db_helpers::extract_column_data($row['value']);
            if ( $col_data['visibility'] !== 'normal' ) {
                continue;
            }
            $value = '';
            foreach ( $course_meta as $row_meta ) {
                if ( $row['variable'] === $row_meta['meta_key'] ) {
                    $value = $row_meta['meta_value'];
                    break;
                }
            }
            $return .= '<p><span class="tp_course_meta_label_' . $row['variable'] . '">' . stripslashes($col_data['title']) . ': </span>' . stripslashes(nl2br($value)) . '</p>';
        }
        return $return;
    }
    
    /**
     * Generates and returns filter for the shortcode [tp_cloud]
     * @param array $filter_parameter       An associative array with filter parameter (user input). The keys are: year, type, author, user
     * @param array $sql_parameter          An assosciative array with SQL search parameter (user, type, exclude, exclude_tags, order)
     * @param array $settings               An assosciative array with settings (permalink, html_anchor,...)
     * @param string $mode                  year, type, author, user or tag, default is year
     * @return string
     * @since 5.0.0
     * @access public
     */
    public static function generate_filter ($filter_parameter, $sql_parameter, $settings, $mode = 'year'){

        $options = '';
        
        // year filter
        if ( $mode === 'year' ) {
            $row = tp_publications::get_years( array( 'user' => $sql_parameter['user'], 
                                                      'type' => $sql_parameter['type'],
                                                      'include' => $sql_parameter['year'],
                                                      'order' => 'DESC', 
                                                      'output_type' => ARRAY_A ) );
            $id = 'pub_year';
            $index = 'year';
            $title = __('All years','teachpress');
        }
        
        // type filter
        if ( $mode === 'type' ) {
            $row = tp_publications::get_used_pubtypes( array( 'user' => $sql_parameter['user'],
                                                              'include' => $sql_parameter['type'],
                                                              'exclude' => isset($sql_parameter['exclude_types']) ? $sql_parameter['exclude_types'] : '') );
            $id = 'pub_type';
            $index = 'type';
            $title = __('All types','teachpress');
        }
        
        // author filter
        if ( $mode === 'author' ) {
            // Use the visible filter o the SQL parameter
            $author_id = ($filter_parameter['show_in_author_filter'] !== '') ? $filter_parameter['show_in_author_filter'] : $sql_parameter['author'];
            
            $row = tp_authors::get_authors( array( 'user' => $sql_parameter['user'],
                                                   'author_id' => $author_id,
                                                   'output_type' => ARRAY_A, 
                                                   'group_by' => true ) );
            $id = 'pub_author';
            $index = 'author_id';
            $title = __('All authors','teachpress');
        }
        
        // user filter
        if ( $mode === 'user' ) {
            $row = tp_publications::get_pub_users( array('output_type' => ARRAY_A) );
            $id = 'pub_user';
            $index = 'user';
            $title = __('All users','teachpress');
        }
        
        // tag filter
        if ( $mode === 'tag' ) {
            $row = tp_tags::get_tags( array( 'output_type' => ARRAY_A, 
                                             'group_by' => true, 
                                             'order' => 'ASC', 
                                             'exclude' => $sql_parameter['exclude_tags'] ) );
            $id = 'pub_tag';
            $index = 'tag_id';
            $title = __('All tags','teachpress');
        }

        // generate option
        foreach ( $row as $row ){
            // Set the values for URL parameters
            $current = ( $row[$index] == $filter_parameter[$mode] && $filter_parameter[$mode] != '0' ) ? 'selected="selected"' : '';
            $tag = ( $mode === 'tag' ) ? $row['tag_id'] : $filter_parameter['tag'] ;
            $year = ( $mode === 'year' ) ? $row['year'] : $filter_parameter['year'];
            $type = ( $mode === 'type' ) ? $row['type'] : $filter_parameter['type'];
            $user = ( $mode === 'user' ) ? $row['user'] : $filter_parameter['user'];
            $author = ( $mode === 'author' ) ? $row['author_id'] : $filter_parameter['author'];
            
            // Set the label for each select option
            if ( $mode === 'type' ) {
                $text = tp_translate_pub_type($row['type'], 'pl');
            }
            else if ( $mode === 'author' ) {
                $text = tp_bibtex::parse_author($row['name'], '', $settings['author_name']);
            }
            else if ( $mode === 'user' ) {
                $user_info = get_userdata( $row['user'] );
                if ( $user_info === false ) {
                    continue;
                }
                $text = $user_info->display_name;
            }
            else if ( $mode === 'tag' ) {
                $text = $row['name'];
            }
            else {
                $text = $row[$index];
            }
            
            // Write the select option
            $options .= '<option value = "tgid=' . $tag. '&amp;yr=' . $year . '&amp;type=' . $type . '&amp;usr=' . $user . '&amp;auth=' . $author . $settings['html_anchor'] . '" ' . $current . '>' . stripslashes($text) . '</option>';
        }

        // clear filter_parameter[$mode]
        $filter_parameter[$mode] = '';
        
        // return filter menu
        return '<select name="' . $id . '" id="' . $id . '" onchange="teachpress_jumpMenu(' . "'" . 'parent' . "'" . ',this, ' . "'" . $settings['permalink'] . "'" . ')">
                   <option value="tgid=' . $filter_parameter['tag'] . '&amp;yr=' . $filter_parameter['year'] . '&amp;type=' . $filter_parameter['type'] . '&amp;usr=' . $filter_parameter['user'] . '&amp;auth=' . $filter_parameter['author'] . '' . $settings['html_anchor'] . '">' . $title . '</option>
                   ' . $options . '
                </select>';
    }
    
    /**
     * Generates the pagination limits for lists
     * @param int $pagination           0 or 1 (pagination is used or not)
     * @param int $entries_per_page     Number of entries per page
     * @param int $form_limit           Current position in the list, which is set by a form 
     * @return array
     * @since 6.0.0
     */
    public static function generate_pagination_limits($pagination, $entries_per_page, $form_limit) {
        
        // Define page variables
        if ( $form_limit != '' ) {
            $current_page = $form_limit;
            if ( $current_page <= 0 ) {
                $current_page = 1;
            }
            $entry_limit = ( $current_page - 1 ) * $entries_per_page;
        }
        else {
            $entry_limit = 0;
            $current_page = 1;
        }
        
        // Define SQL limit
        if ( $pagination === 1 ) {
            $limit = $entry_limit . ',' .  $entries_per_page;
        }
        else {
            $limit = ( $entries_per_page > 0 ) ? $entry_limit . ',' .  $entries_per_page : '';
        }
        
        return array(
            'entry_limit' => $entry_limit,
            'current_page' => $current_page,
            'limit' => $limit
        );
        
    }
    
    /**
     * Generates the list of publications for [tplist], [tpcloud], [tpsearch]
     * @param array $tparray    The array of publications
     * @param object $template  The template object
     * @param array $args       An associative array with options (headline,...)
     * @return string
     * @since 5.0.0
     * @access public
     */
    public static function generate_pub_table($tparray, $template, $args ) {
        $headlines = array();
        if ( $args['headline'] == 1 ) {
            foreach( $args['years'] as $row ) {
                $headlines[$row['year']] = '';
            }
            $pubs = tp_shortcodes::sort_pub_table( $tparray, $template, $headlines , $args );
        }
        elseif ( $args['headline'] == 2 ) {
            $pub_types = tp_publications::get_used_pubtypes( array('user' => $args['user'] ) );
            foreach( $pub_types as $row ) {
                $headlines[$row['type']] = '';
            }
            $pubs = tp_shortcodes::sort_pub_table( $tparray, $template, $headlines, $args );
        }
        else {
            $pubs = tp_shortcodes::sort_pub_table( $tparray, $template, '', $args );
        }
        return $template->get_body($pubs, $args);
    }
    
    /**
     * Returns a tag cloud
     * @param int $user                 The user ID
     * @param array $cloud_settings     An associative array with settings for the cloud (tag_limit, maxsize, minsize)
     * @param array $filter_parameter   An associative array with filter parameter (user input). The keys are: year, type, author, user
     * @param array $sql_parameter      An assosciative array with SQL search parameter (user, type)
     * @param array $settings           An assosciative array with settings (permalink, html_anchor)
     * @return string
     * @since 5.0.0
     * @access public
     */
    public static function generate_tag_cloud ($user, $cloud_settings, $filter_parameter, $sql_parameter, $settings){
        $temp = tp_tags::get_tag_cloud( array('user' => $user, 
                                        'type' => $sql_parameter['type'],
                                        'exclude' => $cloud_settings['hide_tags'],
                                        'number_tags' => $cloud_settings['tag_limit'],
                                        'output_type' => ARRAY_A) );
       $min = $temp["info"]->min;
       $max = $temp["info"]->max;
       // level out the min
       if ($min == 1) {
          $min = 0;
       }
       // Create the cloud
       $tags = '';
       foreach ($temp["tags"] as $tagcloud) {
          $link_url = $settings['permalink'];
          $link_title = "";
          $link_class = "";
          $pub = ( $tagcloud['tagPeak'] == 1 ) ? __('publication', 'teachpress') : __('publications', 'teachpress');
          
          // division through zero check
          $divisor = ( $max - $min === 0 ) ? 1 : $max - $min;

          // calculate the font size
          // max. font size * (current occorence - min occurence) / (max occurence - min occurence)
          $size = floor(( $cloud_settings['maxsize'] *( $tagcloud['tagPeak'] - $min ) / $divisor ));
          // level out the font size
          if ( $size < $cloud_settings['minsize'] ) {
             $size = $cloud_settings['minsize'] ;
          }

          // for current tags
          if ( $filter_parameter['tag'] == $tagcloud['tag_id'] ) {
              $link_class = "teachpress_cloud_active";
              $link_title = __('Delete tag as filter','teachpress');
          }
          else {
              $link_title = $tagcloud['tagPeak'] . " $pub";
              $link_url .= "tgid=" . $tagcloud['tag_id'] . "&amp;";
          }

          // define url
          $link_url .= 'yr=' . $filter_parameter['year'] . '&amp;type=' . $filter_parameter['type'] . '&amp;usr=' . $filter_parameter['user'] . '&amp;auth=' . $filter_parameter['author'] . $settings['html_anchor'];

          $tags .= '<span style="font-size:' . $size . 'px;"><a rel="nofollow" href="' . $link_url . '" title="' . $link_title . '" class="' . $link_class . '">' . stripslashes($tagcloud['name']) . '</a></span> ';
       }
       return $tags;
    }
    
    /**
     * Sort the table lines of a publication table
     * @param array $tparray        Array of publications
     * @param object $template      The template object
     * @param array $headlines      Array of headlines
     * @param array $args           Array of arguments
     * @return string 
     * @since 5.0.0
     * @access public
     */
    public static function sort_pub_table($tparray, $template, $headlines, $args) {
        $publications = '';
        $tpz = $args['number_publications'];

        // with headlines
        if ( $args['headline'] === 1 || $args['headline'] === 2 ) {
            $publications = tp_shortcodes::sort_pub_by_type_or_year($tparray, $template, $tpz, $args, $headlines);
        }
        // with headlines grouped by year then by type
        else if ($args['headline'] === 3) {
            $publications = tp_shortcodes::sort_pub_by_year_type($tparray, $template, $tpz, $args);
        }
        // with headlines grouped by type then by year
        else if ($args['headline'] === 4) {
            $publications = tp_shortcodes::sort_pub_by_type_year($tparray, $template, $tpz, $args);
        }
        // without headlines
        else {
            for ($i = 0; $i < $tpz; $i++) {
                $publications .= $tparray[$i][1];
            }
        }

        return $publications;
    }
    
    /**
     * Sorts the publications by type or by year. This is the default sort function
     * @param array $tparray    The numeric publication array
     * @param object $template  The template object
     * @param int $tpz          The length of $tparray
     * @param array $args       An associative of arguments (colspan)
     * @return string
     * @access private
     * @since 5.0.0
     */
    private static function sort_pub_by_type_or_year($tparray, $template, $tpz, $args, $headlines){
        $return = '';
        $field = ( $args['headline'] === 2 ) ? 2 : 0;
        for ( $i = 0; $i < $tpz; $i++ ) {
            $key = $tparray[$i][$field];
            $headlines[$key] .= $tparray[$i][1];
        }
        
        // custom sort order
        if ( $args['sort_list'] !== '' ) {
            $args['sort_list'] = str_replace(' ', '', $args['sort_list']);
            $sort_list = explode(',', $args['sort_list']);
            $max = count($sort_list);
            $sorted = array();
            for ($i = 0; $i < $max; $i++) {
                if ( array_key_exists($sort_list[$i], $headlines) ) {
                    $sorted[$sort_list[$i]] = $headlines[$sort_list[$i]];
                }
            }
            $headlines = $sorted;
        }
        
        // set headline
        foreach ( $headlines as $key => $value ) {
            if ( $value != '' ) {
                $line_title = ( $args['headline'] === 1 ) ? $key : tp_translate_pub_type($key, 'pl');
                $return .=  $template->get_headline($line_title, $args);
                $return .=  $value;
            }
        }
        return $return;
    }
    
    /**
     * Sorts the publications by type and by year (used for headline type 4)
     * @param array $tparray    The numeric publication array
     * @param object $template  The template object
     * @param int $tpz          The length of $tparray
     * @param array $args       An associative of arguments (colspan)
     * @return string
     * @access private
     * @since 5.0.0
     */
    private static function sort_pub_by_type_year($tparray, $template, $tpz, $args) {
        $return = '';
        $typeHeadlines = array();
        for ($i = 0; $i < $tpz; $i++) {
            $keyYear = $tparray[$i][0];
            $keyType = $tparray[$i][2];
            $pubVal  = $tparray[$i][1];
            if(!array_key_exists($keyType, $typeHeadlines)) {
                $typeHeadlines[$keyType] = array($keyYear => $pubVal); 
            }
            else if(!array_key_exists($keyYear, $typeHeadlines[$keyType])) {
                $typeHeadlines[$keyType][$keyYear] = $pubVal;
            }
            else {
                $typeHeadlines[$keyType][$keyYear] .= $pubVal;
            }
        }
        foreach ( $typeHeadlines as $type => $yearHeadlines ) {
            $return .= $template->get_headline( tp_translate_pub_type($type, 'pl'), $args );
            foreach($yearHeadlines as $year => $pubValue) {
                if ($pubValue != '' ) {
                    $return .= $template->get_headline( $year, $args );
                    $return .= $pubValue;
                }
            }
        }
        return $return;
    }
    
    /**
     * Sorts the publications by year and by type (used for headline type 3)
     * @param array $tparray    The numeric publication array
     * @param object $template  The template object
     * @param int $tpz          The length of $tparray
     * @param array $args       An associative of arguments (colspan)
     * @return string
     * @access private
     * @since 5.0.0
     */
    private static function sort_pub_by_year_type ($tparray, $template, $tpz, $args) {
        $return = '';
        $yearHeadlines = array();
        for ($i = 0; $i < $tpz; $i++) {
            $keyYear = $tparray[$i][0];
            $keyType = $tparray[$i][2];
            if(!array_key_exists($keyYear, $yearHeadlines)) {
                $yearHeadlines[$keyYear] = array($keyType => '');
            }
            else if(!array_key_exists($keyType, $yearHeadlines[$keyYear])) {
                $yearHeadlines[$keyYear][$keyType] = '';
            }
            $yearHeadlines[$keyYear][$keyType] .= $tparray[$i][1];
        }

        foreach ( $yearHeadlines as $year => $typeHeadlines ) {
            $return .= $template->get_headline($year, $args);
            foreach($typeHeadlines as $type => $value) {
                if ($value != '' ) {
                    $return .= $template->get_headline( tp_translate_pub_type($type, 'pl'), $args );
                    $return .=  $value;
                }
            }
        }
        return $return;
    }
    
    /**
     * Sets and returns the publication data. Used for tp_bibtex, tp_abstract and tp_links shortcodes
     * @param array $param
     * @param array $tp_single_publication
     * @return array
     * @since 6.0.0
     * @access public
     */
    public static function set_publication ($param, $tp_single_publication) {
        if ( $param['key'] != '' ) {
            return tp_publications::get_publication_by_key($param['key'], ARRAY_A);
        } 
        elseif ( $param['id'] != 0 ) {
            return tp_publications::get_publication($param['id'], ARRAY_A);
        } 
        else {
            return $tp_single_publication;
        }
    }
    
}

/** 
 * Shows an overview of courses
 * 
 * possible values for $atts:
 *      image (STRING)      left, right, bottom or none, default: none
 *      image_size (INT)    default: 0
 *      headline (INT)      0 for hide headline, 1 for show headline (default:1)
 *      text (STRING)       a custom text under the headline
 *      term (STRING)       the term/semester you want to show
 * 
 * @param array $atts
 * @param string $semester (GET)
 * @return string
 * @since 2.0.0
*/
function tp_courselist_shortcode($atts) {	
    $param = shortcode_atts(array(
       'image' => 'none',
       'image_size' => 0,
       'headline' => 1,
       'text' => '',
       'term' => ''
    ), $atts);
    $image = htmlspecialchars($param['image']);
    $text = htmlspecialchars($param['text']);
    $term = htmlspecialchars($param['term']);
    $image_size = intval($param['image_size']);
    $headline = intval($param['headline']);

    $url = array(
        'post_id' => get_the_id()
    );

    // hanlde permalinks
    if ( !get_option('permalink_structure') ) {
        $page = ( is_page() ) ? 'page_id' : 'p';
        $page = '<input type="hidden" name="' . $page . '" id="' . $page . '" value="' . $url["post_id"] . '"/>';
    }
    else {
        $page = '';
    }
    
    // define term
    if ( isset( $_GET['semester'] ) ) {
        $sem = htmlspecialchars($_GET['semester']);
    }
    elseif ( $term != '' ) {
        $sem = $term;
    }
    else {
        $sem = get_tp_option('sem');
    }
   
    $rtn = '<div id="tpcourselist">';
    if ($headline === 1) {
         $rtn .= '<h2>' . __('Courses for the','teachpress') . ' ' . stripslashes($sem) . '</h2>';
    }
    $rtn .= '' . $text . '
               <form name="lvs" method="get" action="' . esc_url($_SERVER['REQUEST_URI']) . '">
               ' . $page . '		
               <div class="tp_auswahl"><label for="semester">' . __('Select the term','teachpress') . '</label> <select name="semester" id="semester" title="' . __('Select the term','teachpress') . '">';
    $rowsem = get_tp_options('semester');
    foreach( $rowsem as $rowsem ) { 
        $current = ($rowsem->value == $sem) ? 'selected="selected"' : '';
        $rtn .= '<option value="' . $rowsem->value . '" ' . $current . '>' . stripslashes($rowsem->value) . '</option>';
    }
    $rtn .= '</select>
           <input type="submit" name="start" value="' . __('Show','teachpress') . '" id="teachpress_submit" class="button-secondary"/>
    </div>';
    $rtn2 = '';
    $row = tp_courses::get_courses( array('semester' => $sem, 'parent' => 0, 'visibility' => '1,2') );
    if ( count($row) != 0 ){
        foreach($row as $row) {
            $rtn2 .= tp_shortcodes::get_courselist_line ($row, $image, $image_size, $sem);
        } 
    }
    else {
        $rtn2 = '<tr><td class="teachpress_message">' . __('Sorry, no entries matched your criteria.','teachpress') . '</td></tr>';
    }
    $rtn2 = '<table class="teachpress_course_list">' . $rtn2 . '</table>';
    $rtn3 = '</form></div>';
    return $rtn . $rtn2 . $rtn3;
}

/**
 * Displays the attached documents of a course
 * 
 * possible values of $atts:
 *      id (INT)                ID of the course 
 *      linkclass (STRING)      The name of the html class for document links, default is: linksecure
 *      date_format (STRING)    Default: d.m.Y
 *      show_date (INT)         1 (date is visible) or 0, default is: 1
 *      numbered (INT)          1 (use numbering) or 0, default is: 0
 *      headline (INT)          1 (display headline) or 0, default is: 1
 * 
 * @param array $atts
 * @since 5.0.0
 */
function tp_coursedocs_shortcode($atts) {
    $param = shortcode_atts(array(
       'id' => '',
       'link_class' => 'linksecure',
       'date_format' => 'd.m.Y',
       'show_date' => 1,
       'numbered' => 0,
       'headline' => 1
    ), $atts);
    $course_id = intval($param['id']);
    $headline = intval($param['headline']);
    $link_class = htmlspecialchars($param['link_class']);
    $date_format = htmlspecialchars($param['date_format']);
    $show_date = intval($param['show_date']);
    $numbered = intval($param['numbered']);
    $upload_dir = wp_upload_dir();
    $documents = tp_documents::get_documents($course_id);
    
    if ( $headline === 1 ) {
        $a = '<div class="tp_course_headline">' . __('Documents','teachpress') . '</div>';
    }
    
    if ( count($documents) === 0 ) {
        return $a;
    }
    
    $num = 1;
    $body = '<table class="tp_coursedocs">';
    foreach ( $documents as $row ) {
        $body .= '<tr>';
        if ( $row['path'] === '' ) {
            $body .= tp_shortcodes::get_coursedocs_headline($row, $numbered, $show_date);
            $num = 1;
        }
        else {
            $body .= tp_shortcodes::get_coursedocs_line($row, $upload_dir, $link_class, $date_format, $numbered, $num, $show_date);
            $num++;
        }
        $body .= '</tr>';
    }
    $body .= '</table>';
    return $a . $body;
}

/** 
 * Displays information about a single course and his childs
 * 
 * possible values of $atts:
 *       id (INT)           -   ID of the course 
 *       show_meta (INT)    -   Display course meta data (1) or not (0), default is 1
 * 
 * @param array $atts
 * @return string
 * @since 5.0.0
*/
function tp_courseinfo_shortcode($atts) {
    $param = shortcode_atts(array(
       'id' => 0,
       'show_meta' => 1
    ), $atts);
    $id = intval($param['id']);
    $show_meta = intval($param['show_meta']);
    
    if ( $id === 0 ) {
        return;
    }
    
    $course = tp_courses::get_course($id);
    $fields = get_tp_options('teachpress_courses','`setting_id` ASC', ARRAY_A);
    $v_test = $course->name;
    $body = '';
    $head = '<div class="tp_course_headline">' . __('Date(s)','teachpress') . '</div>';
    $head .= '<table class="tp_courseinfo">';
    
    $head .= '<tr>';
    $head .= '<td class="tp_courseinfo_type"><strong>' . stripslashes($course->type) . '</strong></td>';
    $head .= '<td class="tp_courseinfo_main">';
    $head .= '<p>' . stripslashes($course->date) . ' ' . stripslashes($course->room) . '</p>';
    $head .= '<p>' . stripslashes(nl2br($course->comment)) . '</p>';
    if ( $show_meta === 1 ) {
        $head .= tp_shortcodes::get_coursemeta_line($id, $fields);
    }
    $head .= '</td>';
    $head .= '<td clas="tp_courseinfo_lecturer">' . stripslashes($course->lecturer) . '</td>';
    $head .= '</tr>';
    
    // Search the child courses
    $row = tp_courses::get_courses( array('parent' => $id, 'visible' => '1,2', 'order' => 'name, course_id') );
    foreach($row as $row) {
        // if parent name = child name
        if ($v_test == $row->name) {
            $row->name = $row->type;
        }
        $body .= '<tr>';
        $body .= '<td class="tp_courseinfo_type"><strong>' . stripslashes($row->name) . '</strong></td>';
        $body .= '<td class="tp_courseinfo_meta">';
        $body .= '<p>' . stripslashes($row->date) . ' ' . stripslashes($row->room) . '</p>';
        $body .= '<p>' . stripslashes($row->comment) . '</p>';
        if ( $show_meta === 1 ) {
            $body .= tp_shortcodes::get_coursemeta_line($id, $fields);
        }
        $body .= '</td>';
        $body .= '<td class="tp_courseinfo_lecturer">' . stripslashes($row->lecturer) . '</td>';
        $body .= '</tr>';
    } 
    return $head . $body . '</table>';
}

/**
 * Prints a citation link
 * 
 * possible values of $atts:
 *      id (INT)            - ID of the publication
 *      key (STRING)        - BibTeX key of a publication
 * 
 * @param array $atts
 * @return string
 * @since 6.0.0
 */
function tp_cite_shortcode ($atts) {
    global $tp_cite_object;
    $param = shortcode_atts(array(
       'id' => 0,
       'key' => ''
    ), $atts);
    
    // Load cite object
    if ( !isset($tp_cite_object) ) {
        $tp_cite_object = new tp_cite_object;
    }
    
    // Check parameter
    if ( $param['key'] != '' ) {
        $publication = tp_publications::get_publication_by_key($param['key'], ARRAY_A);
    }
    else {
        $publication = tp_publications::get_publication($param['id'], ARRAY_A);
    }
    
    // Count ref number
    $count = $tp_cite_object->get_count();
    
    // Add ref to cite object
    $tp_cite_object->add_ref($publication);
    
    // Return
    return '<sup><a href="#tp_cite_' . $publication['pub_id'] . '">[' . ( $count + 1 ) . ']</a></sup>';
}

/**
 * Prints the references
 * 
 * possible values of $atts:
 *      author_name (STRING)    last, initials or old, default: simple
 *      author_name (STRING)    last, initials or old, default: initials
 *      author_separator (STRING)   The separator for author names, default: ;
 *      editor_separator (STRING)   The separator for editor names, default: ;
 *      date_format (STRING)    the format for date; needed for the types: presentations, online; default: d.m.Y
 *      show_links (INT)        0 (false) or 1 (true), default: 0
 * @param array $atts
 * @return string
 * @since 6.0.0
 */
function tp_ref_shortcode($atts) {
    global $tp_cite_object;
    
    // shortcode parameter defaults
    $param = shortcode_atts(array(
       'author_name' => 'simple',
       'editor_name' => 'initials',
       'author_separator' => ',',
       'editor_separator' => ';',
       'date_format' => 'd.m.Y',
       'show_links'=> 0
    ), $atts);
    
    // define settings
    $settings = array(
       'author_name' => htmlspecialchars($param['author_name']),
       'editor_name' => htmlspecialchars($param['editor_name']),
       'author_separator' => htmlspecialchars($param['author_separator']),
       'editor_separator' => htmlspecialchars($param['editor_separator']),
       'date_format' => htmlspecialchars($param['date_format']),
       'style' => 'simple',
       'title_ref' => 'links',
       'link_style' => ($param['show_links'] == 1) ? 'direct' : 'none',
       'use_span' => false
    );
    
    // define reference part
    $references = $tp_cite_object->get_ref();
    
    $ret = '<h3 class="teachpress_ref_headline">' . __('References','teachpress') . '</h3>';
    $ret .= '<ol>';
    foreach ( $references as $row ) {
        $ret .= '<li id="tp_cite_' . $row['pub_id'] . '" class="tp_cite_entry"><span class="tp_single_author">' . stripslashes($row['author']) . '</span><span class="tp_single_year"> (' . $row['year'] . ')</span>: <span class="tp_single_title">' . tp_html_publication_template::prepare_publication_title($row, $settings, 1) . '</span>. <span class="tp_single_additional">' . tp_html::get_publication_meta_row($row, $settings) . '</span></li>';
    }
    $ret .= '</ol>';
    return $ret;
}

/** 
 * Shorcode for a single publication
 * 
 * possible values of $atts:
 *      id (INT)                id of a publication
 *      key (STRING)            bibtex key of a publication 
 *      author_name (STRING)    last, initials or old, default: simple
 *      author_name (STRING)    last, initials or old, default: last
 *      author_separator (STRING)   The separator for author names, default: ;
 *      editor_separator (STRING)   The separator for editor names, default: ;
 *      date_format (STRING)    the format for date; needed for the types: presentations, online; default: d.m.Y
 *      image (STRING)          none, left or right; default: none
 *      image_size (STRING)     image width in px; default: 0
 *      link (STRING)           Set it to "true" if you want to show a link in addition of the publication title. If there are more than one link, the first one is used.                 
 * 
 * @param array $atts
 * @return string
 * @since 2.0.0
*/ 
function tp_single_shortcode ($atts) {
    global $tp_single_publication;
    $param = shortcode_atts(array(
       'id' => 0,
       'key' => '',
       'author_name' => 'simple',
       'author_separator' => ',',
       'editor_separator' => ';',
       'editor_name' => 'last',
       'date_format' => 'd.m.Y',
       'image' => 'none',
       'image_size' => 0,
       'link' => ''
    ), $atts);

    $settings = array(
       'author_name' => htmlspecialchars($param['author_name']),
       'editor_name' => htmlspecialchars($param['editor_name']),
       'author_separator' => htmlspecialchars($param['author_separator']),
       'editor_separator' => htmlspecialchars($param['editor_separator']),
       'date_format' => htmlspecialchars($param['date_format']),
       'style' => 'simple',
       'use_span' => true
    );
    
    // Set publication
    if ( $param['key'] != '' ) {
        $publication = tp_publications::get_publication_by_key($param['key'], ARRAY_A);
    }
    else {
        $publication = tp_publications::get_publication($param['id'], ARRAY_A);
    }
    $tp_single_publication = $publication;
    
    // Set author name
    if ( $publication['type'] === 'collection' || $publication['type'] === 'periodical' || ( $publication['author'] === '' && $publication['editor'] !== '' ) ) {
        $author = tp_bibtex::parse_author($publication['editor'], $settings['author_separator'], $settings['editor_name'] ) . ' (' . __('Ed.','teachpress') . ')';
    }
    else {
        $author = tp_bibtex::parse_author($publication['author'], $settings['author_separator'], $settings['author_name'] );
    }
    
    $image_size = intval($param['image_size']);
    
    $asg = '<div class="tp_single_publication">';
    
    // add image
    if ( ( $param['image'] === 'left' || $param['image'] === 'right' ) && $publication['image_url'] != '' ) {
        $class = ( $param['image'] === 'left' ) ? 'tp_single_image_left' : 'tp_single_image_right';
        $asg .= '<div class="' . $class . '"><img name="' . $publication['title'] . '" src="' . $publication['image_url'] . '" width="' . $image_size .'" alt="" /></div>';
    }
    
    // define title
    if ( $param['link'] !== '' && $publication['url'] !== '' ) {
        // Use the first link in url field without the original title
        $url = explode(chr(13) . chr(10), $publication['url']);
        $parts = explode(', ',$url[0]);
        $parts[0] = trim( $parts[0] );
        $title = '<a href="' . $parts[0] . '">' . tp_html::prepare_title($publication['title'], 'decode') . '</a>';
    }
    else {
        $title = tp_html::prepare_title($publication['title'], 'decode');
    }
    $asg .= '<span class="tp_single_author">' . stripslashes($author) . ': </span> <span class="tp_single_title">' . $title . '</span>. <span class="tp_single_additional">' . tp_html::get_publication_meta_row($publication, $settings) . '</span>';
    $asg .= '</div>';
    return $asg;
}

/** 
 * Shortcode for displaying the BibTeX code of a single publication
 * 
 * possible values of $atts:
 *      id (INT)        id of a publication
 *      key (STRING)    bibtex key of a publication 
 * 
 * If neither is given, the publication of the most recent [tpsingle] will be reused
 * 
 * @param array $atts
 * @return string
 * @since 4.2.0
*/ 
function tp_bibtex_shortcode ($atts) {
    global $tp_single_publication;
    $param = shortcode_atts(array(
       'id' => 0,
       'key' => '',
    ), $atts);
    
    $convert_bibtex = ( get_tp_option('convert_bibtex') == '1' ) ? true : false;
    $publication = tp_shortcodes::set_publication($param, $tp_single_publication);
    
    $tags = tp_tags::get_tags( array('pub_id' => $publication['pub_id'], 'output_type' => ARRAY_A) );
    
    return '<h2 class="tp_bibtex">BibTeX (<a href="' . home_url() . '?feed=tp_pub_bibtex&amp;key=' . $publication['bibtex'] . '">Download</a>)</h2><pre class="tp_bibtex">' . tp_bibtex::get_single_publication_bibtex($publication, $tags, $convert_bibtex) . '</pre>';
}

/** 
 * Shortcode for displaying the abstract of a single publication
 * 
 * possible values of $atts:
 *      id (INT)        id of a publication
 *      key (STRING)    bibtex key of a publication 
 * 
 * If neither is given, the publication of the most recent [tpsingle] will be reused
 * 
 * @param array $atts
 * @return string
 * @since 4.2.0
*/ 
function tp_abstract_shortcode ($atts) {
    global $tp_single_publication;
    $param = shortcode_atts(array(
       'id' => 0,
       'key' => '',
    ), $atts);

    $publication = tp_shortcodes::set_publication($param, $tp_single_publication);

    if ( isset($publication['abstract']) ) {
        return '<h2 class="tp_abstract">' . __('Abstract','teachpress') . '</h2><p class="tp_abstract">' . tp_html::prepare_text($publication['abstract']) . '</p>';
    }
    return;
}

/**
 * Shortcode for displaying the related websites (url) of a publication 
 * 
 * possible values of $atts:
 *      id (INT)        id of a publication
 *      key (STRING)    bibtex key of a publication 
 * 
 * If neither is given, the publication of the most recent [tpsingle] will be reused
 * 
 * @param array $atts
 * @return string
 * @scine 4.2.0
 */
function tp_links_shortcode ($atts) {
    global $tp_single_publication;
    $param = shortcode_atts(array(
       'id' => 0,
       'key' => '',
    ), $atts);
    
    $publication = tp_shortcodes::set_publication($param, $tp_single_publication);
    
    if ( isset($publication['url']) ) {
        return '<h2 class="tp_links">' . __('Links','teachpress') . '</h2><p class="tp_abstract">' . tp_html_publication_template::prepare_url($publication['url'], $publication['doi'], 'list') . '</p>';
    } 
    return;
    
}

/** 
 * Shortcode for displaying a publication list with tag cloud
 * 
 * Parameters for the array $atts:
 *      user (STRING)               the ID of on or more users (separated by comma)
 *      type (STRING)               the publication types you want to show (separated by comma)
 *      author (STRING)             author IDs (separated by comma)
 *      year (STRING)               one or more years (separated by comma)
 *      exclude (INT)               one or more IDs of publications you don't want to show (separated by comma)
 *      include_editor_as_author (INT)
 *      order (STRING)              title, year, bibtex or type, default: date DESC
 *      headline (INT)              show headlines with years(1), with publication types(2), with years and types (3), with types and years (4) or not(0), default: 1
 *      maxsize (INT)               maximal font size for the tag cloud, default: 35
 *      minsize (INT)               minimal font size for the tag cloud, default: 11
 *      tag_limit (INT)             number of tags, default: 30
 *      hide_tags (STRING)          ids of the tags you want to hide from your users (separated by comma)
 *      exclude_tags (STRING)       similar to hide_tags but with influence on publications; if exclude_tags is defined hide_tags will be ignored
 *      exclude_types (STRING)      name of the publication types you want to exclude (separated by comma)
 *      image (STRING)              none, left, right or bottom, default: none 
 *      image_size (INT)            max. Image size, default: 0
 *      image_link (STRING)         none, self or post (defalt: none)
 *      anchor (INT)                0 (false) or 1 (true), default: 1
 *      author_name (STRING)        simple, last, initials or old, default: last
 *      editor_name (STRING)        simple, last, initials or old, default: last
 *      author_separator (STRING)   The separator for author names
 *      editor_separator (STRING)   The separator for editor names
 *      style (STRING)              numbered, numbered_desc or none, default: none
 *      template (STRING)           the key of the template, default: tp_template_2016
 *      title_ref (STRING)          links or abstract, default: links
 *      link_style (STRING)         inline, direct or images, default: inline
 *      date_format (STRING)        the format for date; needed for the types: presentations, online; default: d.m.Y
 *      pagination (INT)            activate pagination (1) or not (0), default: 1
 *      entries_per_page (INT)      number of publications per page (pagination must be set to 1), default: 30
 *      sort_list (STRING)          a list of publication types (separated by comma) which overwrites the default sort order for headline = 2
 *      show_tags_as (STRING)       cloud, pulldown or none, default: cloud
 *      show_bibtex (INT)           0 (false) or 1 (true), default: 1
 *      show_author_filter (INT)    0 (false) or 1 (true), default: 1
 *      show_in_author_filter (STRING) Can be used to manage the visisble authors in the author filter. Uses the author IDs (separated by comma)
 *      show_user_filter (INT)      0 (false) or 1 (true), default: 1
 *      show_type_filter (INT)      0 (false) or 1 (true), default: 1
 *      container_suffix (STRING)   a suffix which can optionally set to modify container IDs in publication lists. It's not set by default.
 *      show_altmetric_donut (INT)  0 (false) or 1 (true), default: 0
 *      show_altmetric_entry (INT)  0 (false) or 1 (true), default: 0
 * 
 * WARNINGS: 
 *      "id" has been removed with teachPress 4.0.0, please use "user" instead!
 * 
 * Parameters from $_GET: 
 *      $yr (INT)              Year 
 *      $type (STRING)         Publication type 
 *      $auth (INT)            Author ID
 *      $tg (INT)              Tag ID
 *      $usr (INT)             User ID
 * 
 * @param array $atts
 * @return string
 * @since 0.10.0
*/
function tp_cloud_shortcode($atts) {
    $atts = shortcode_atts(array(
        'user' => '',
        'type' => '',
        'author' => '',
        'year' => '',
        'exclude' => '', 
        'include_editor_as_author' => 1,
        'order' => 'date DESC',
        'headline' => 1, 
        'maxsize' => 35,
        'minsize' => 11,
        'tag_limit' => 30,
        'hide_tags' => '',
        'exclude_tags' => '',
        'exclude_types' => '',
        'image' => 'none',
        'image_size' => 0,
        'image_link' => 'none',
        'anchor' => 1,
        'author_name' => 'initials',
        'editor_name' => 'initials',
        'author_separator' => ';',
        'editor_separator' => ';',
        'style' => 'none',
        'template' => 'tp_template_2016',
        'title_ref' => 'links',
        'link_style' => 'inline',
        'date_format' => 'd.m.Y',
        'pagination' => 1,
        'entries_per_page' => 50,
        'sort_list' => '',
        'show_tags_as' => 'cloud',
        'show_author_filter' => 1,
        'show_in_author_filter' => '',
        'show_type_filter' => 1,
        'show_user_filter' => 1,
        'show_bibtex' => 1,
        'container_suffix' => '',
        'show_altmetric_donut' => 0,
        'show_altmetric_entry' => 0
    ), $atts);
   
    $settings = array(
        'author_name' => htmlspecialchars($atts['author_name']),
        'editor_name' => htmlspecialchars($atts['editor_name']),
        'author_separator' => htmlspecialchars($atts['author_separator']),
        'editor_separator' => htmlspecialchars($atts['editor_separator']),
        'headline' => intval($atts['headline']),
        'style' => htmlspecialchars($atts['style']),
        'template' => htmlspecialchars($atts['template']),
        'image' => htmlspecialchars($atts['image']),
        'image_link' => htmlspecialchars($atts['image_link']),
        'link_style' => htmlspecialchars($atts['link_style']),
        'title_ref' => htmlspecialchars($atts['title_ref']),
        'html_anchor' => ( $atts['anchor'] == '1' ) ? '#tppubs' : '',
        'date_format' => htmlspecialchars($atts['date_format']),
        'permalink' => ( get_option('permalink_structure') ) ? get_permalink() . "?" : get_permalink() . "&amp;",
        'convert_bibtex' => ( get_tp_option('convert_bibtex') == '1' ) ? true : false,
        'pagination' => intval($atts['pagination']),
        'entries_per_page' => intval($atts['entries_per_page']),
        'sort_list' => htmlspecialchars($atts['sort_list']),
        'show_author_filter' => ( $atts['show_author_filter'] == '1' ) ? true : false,
        'show_type_filter' => ( $atts['show_type_filter'] == '1' ) ? true : false,
        'show_user_filter' => ( $atts['show_user_filter'] == '1' ) ? true : false,
        'show_bibtex' => ( $atts['show_bibtex'] == '1' ) ? true : false,
        'with_tags' => 1,
        'container_suffix' => htmlspecialchars($atts['container_suffix']),
        'show_altmetric_entry' => ($atts['show_altmetric_entry'] == '1') ? true : false,
        'show_altmetric_donut' => ($atts['show_altmetric_donut'] == '1') ? true : false
    );

    $cloud_settings = array (
        'show_tags_as' => htmlspecialchars($atts['show_tags_as']),
        'tag_limit' => intval($atts['tag_limit']),
        'hide_tags' => htmlspecialchars($atts['hide_tags']),
        'maxsize' => intval($atts['maxsize']),
        'minsize' => intval($atts['minsize'])
    );
    
    $filter_parameter = array(
        'tag' => ( isset ($_GET['tgid']) && $_GET['tgid'] != '' ) ? intval($_GET['tgid']) : '',
        'year' => ( isset ($_GET['yr']) && $_GET['yr'] != '' ) ? intval($_GET['yr']) : '',
        'type' => isset ($_GET['type']) ? htmlspecialchars( $_GET['type'] ) : '',
        'author' => ( isset ($_GET['auth']) && $_GET['auth'] != '' ) ? intval($_GET['auth']) : '',
        'user' => ( isset ($_GET['usr']) && $_GET['usr'] != '' ) ? intval($_GET['usr']) : '',
        'show_in_author_filter' => htmlspecialchars($atts['show_in_author_filter'])
    );
    
    $sql_parameter = array (
        'user' => htmlspecialchars($atts['user']),
        'type' => htmlspecialchars($atts['type']),
        'author' => htmlspecialchars($atts['author']),
        'year' => htmlspecialchars($atts['year']),
        'exclude' => htmlspecialchars($atts['exclude']),
        'exclude_tags' => htmlspecialchars($atts['exclude_tags']),
        'exclude_types' => htmlspecialchars($atts['exclude_types']),
        'order' => htmlspecialchars($atts['order']),
    );

    // user: overwrite filter with the shortcode value if the filter param is unset
    if ( $filter_parameter['user'] === '' ) {
        $filter_parameter['user'] = htmlspecialchars($atts['user']);
    }
    
    // types: overwrite filter with the shortcode value if the filter param is unset
    if ( $filter_parameter['type'] === '' ) {
        $filter_parameter['type'] = htmlspecialchars($atts['type']);
    }
    
    // authors: overwrite filter with the shortcode value if the filter param is unset
    if ( $filter_parameter['author'] === '' ) {
       $filter_parameter['author'] = htmlspecialchars($atts['author']);
    }
    
    // years: overwrite filter with the shortcode value if the filter param is unset
    if ( $filter_parameter['year'] === '' ) {
       $filter_parameter['year'] = htmlspecialchars($atts['year']);
    } 
   
    // Handle limits for pagination   
    $form_limit = ( isset($_GET['limit']) ) ? intval($_GET['limit']) : '';
    $pagination_limits = tp_shortcodes::generate_pagination_limits($settings['pagination'], $settings['entries_per_page'], $form_limit);

    // ignore hide_tags if exclude_tags is given 
    if ( $sql_parameter['exclude_tags'] != '' ) {
        $atts['hide_tags'] = $sql_parameter['exclude_tags'];
    }

    /*************/
    /* Tag cloud */
    /*************/
    $asg = '';
    if ( $cloud_settings['show_tags_as'] === 'cloud' ) {
        $asg = tp_shortcodes::generate_tag_cloud($atts['user'], $cloud_settings, $filter_parameter, $sql_parameter, $settings);
    }
    
    /**********/ 
    /* Filter */
    /**********/
    $filter = '';
    
    // Filter year
    if ( $atts['year'] == '' || strpos($atts['year'], ',') !== false ) {
        $filter .= tp_shortcodes::generate_filter($filter_parameter, $sql_parameter, $settings, 'year');
    }

    // Filter type
    if ( ( $atts['type'] == '' || strpos($atts['type'], ',') !== false ) && 
            $settings['show_type_filter'] === true ) {
        $filter .= tp_shortcodes::generate_filter($filter_parameter, $sql_parameter, $settings, 'type');
    }
    
    // Filter tag
    if ( $cloud_settings['show_tags_as'] === 'pulldown' ) {
        $filter .= tp_shortcodes::generate_filter($filter_parameter, $sql_parameter, $settings, 'tag');
    }

    // Filter author
    if ( ( $atts['author'] == '' || strpos($atts['author'], ',') !== false ) && 
            $settings['show_author_filter'] === true ) {
        $filter .= tp_shortcodes::generate_filter($filter_parameter, $sql_parameter, $settings, 'author');
    }
    
    // Filter user
    if ( ( $atts['user'] == '' || strpos($atts['user'], ',') !== false ) &&
            $settings['show_user_filter'] === true ) {
        $filter .= tp_shortcodes::generate_filter($filter_parameter, $sql_parameter, $settings, 'user');
    }

    // Endformat
    if ($filter_parameter['year'] == '' && ( $filter_parameter['type'] == '' || $filter_parameter['type'] == $atts['type'] ) && ( $filter_parameter['user'] == '' || $filter_parameter['user'] == $atts['user'] ) && $filter_parameter['author'] == '' && $filter_parameter['tag'] == '') {
        $showall = '';
    }
    else {
        $showall = '<a rel="nofollow" href="' . $settings['permalink'] . $settings['html_anchor'] . '" title="' . __('Show all','teachpress') . '">' . __('Show all','teachpress') . '</a>';
    }
    
    // complete the header (tag cloud + filter)
    $part1 = '<a name="tppubs" id="tppubs"></a><div class="teachpress_cloud">' . $asg . '</div><div class="teachpress_filter">' . $filter . '</div><p style="text-align:center">' . $showall . '</p>';

    /************************/
    /* List of publications */
    /************************/

    // change the id
    if ( $filter_parameter['user'] != 0) {
        $atts['user'] = $filter_parameter['user'];
    }

    // Handle headline/order settings
    if ( $settings['headline'] === 2 ) {
        $sql_parameter['order'] = "type ASC, date DESC"; 
    }
    if ( $settings['headline'] === 3 || $settings['headline'] === 4 ) {
        $sql_parameter['order'] = "year DESC, type ASC, date DESC";
    }
    
    $args = array(
        'tag' => $filter_parameter['tag'], 
        'year' => $filter_parameter['year'], 
        'type' => $filter_parameter['type'], 
        'user' => $filter_parameter['user'], 
        'author_id' => $filter_parameter['author'],
        'order' => $sql_parameter['order'], 
        'exclude' => $sql_parameter['exclude'],
        'exclude_tags' => $sql_parameter['exclude_tags'],
        'exclude_types' => $sql_parameter['exclude_types'],
        'include_editor_as_author' => ($atts['include_editor_as_author'] == 1) ? true : false,
        'limit' => $pagination_limits['limit'],
        'output_type' => ARRAY_A);

    $all_tags = tp_tags::get_tags( array('exclude' => $atts['hide_tags'], 'output_type' => ARRAY_A) );
    $number_entries = tp_publications::get_publications($args, true);
    $row = tp_publications::get_publications( $args );
    $tpz = 0;
    $count = count($row);
    $tparray = array();
    
    // colspan setup
    $colspan = '';
    if ($settings['image'] == 'left' || $settings['image'] == 'right' || $settings['show_altmetric_donut']) {
        $settings['pad_size'] = intval($atts['image_size']) + 5;
        $colspan = ' colspan="2"';
    }
    
    // Load template
    $template = tp_load_template($settings['template']);
    if ( $template === false ) {
        $template = tp_load_template('tp_template_orig');
    }
    
    // Create array of publications
    foreach ($row as $row) {
        $number = tp_html_publication_template::prepare_publication_number($number_entries, $tpz, $pagination_limits['entry_limit'], $atts['style']);
        $tparray[$tpz][0] = $row['year'] ;
        
        // teachPress style
        $tparray[$tpz][1] = tp_html_publication_template::get_single($row, $all_tags, $settings, $template, $number);
        
        if ( 2 <= $settings['headline'] && $settings['headline'] <= 4 ) {
            $tparray[$tpz][2] = $row['type'] ;
        }
        $tpz++;
    }
    
    // Sort the array
    // If there are publications
    if ( $tpz != 0 ) {
        $part2 = '';
        $link_attributes = 'tgid=' . $filter_parameter['tag'] . '&amp;yr=' . $filter_parameter['year'] . '&amp;type=' . $filter_parameter['type'] . '&amp;usr=' . $filter_parameter['user'] . '&amp;auth=' . $filter_parameter['author'] . $settings['html_anchor'];
        $menu = ( $settings['pagination'] === 1 ) ? tp_page_menu(array('number_entries' => $number_entries,
                                                                       'entries_per_page' => $settings['entries_per_page'],
                                                                       'current_page' => $pagination_limits['current_page'],
                                                                       'entry_limit' => $pagination_limits['entry_limit'],
                                                                       'page_link' => $settings['permalink'],
                                                                       'link_attributes' => $link_attributes,
                                                                       'mode' => 'bottom',
                                                                       'before' => '<div class="tablenav">',
                                                                       'after' => '</div>')) : '';
        $part2 .= $menu;
        $row_year = tp_publications::get_years( 
                        array( 'user' => $sql_parameter['user'], 
                               'type' => $sql_parameter['type'], 
                               'order' => 'DESC', 
                               'output_type' => ARRAY_A ) );
        
        $part2 .= tp_shortcodes::generate_pub_table( 
                        $tparray, 
                        $template, 
                        array( 'number_publications' => $tpz, 
                               'headline' => $settings['headline'],
                               'years' => $row_year,
                               'colspan' => $colspan,
                               'user' => $atts['user'],
                               'sort_list' => $settings['sort_list'] ) );
        $part2 .= $menu;
    }
    // If there are no publications founded
    else {
        $part2 = '<div class="teachpress_list"><p class="teachpress_mistake">' . __('Sorry, no publications matched your criteria.','teachpress') . '</p></div>';
    }
    
    // Return
    return $part1 . $part2;
}

/** 
 * Shortcode for displaying apublication list without filters
 * 
 * possible values for $atts:
 *      user (STRING)               wp user IDs (separated by comma)
 *      tag (STRING)                tag IDs (separated by comma)
 *      type (STRING)               publication types (separated by comma)
 *      author (STRING)             author IDs (separated by comma)
 *      exclude (STRING)            a string with one or more IDs of publication you don't want to display
 *      include (STRING)            a string with one or more IDs of publication you want to display
 *      include_editor_as_author (INT)
 *      year (STRING)               the publication years (separated by comma)
 *      exclude_tags (STRING)       excludes all publications with the given tag IDs (separated by comma)
 *      exclude_types (STRING)      names of the publication types you want to exclude (separated by comma)
 *      order (STRING)              title, year, bibtex or type, default: date DESC
 *      headline (INT)              show headlines with years(1), with publication types(2), with years and types (3), with types and years (4) or not(0), default: 1
 *      image (STRING)              none, left, right or bottom, default: none 
 *      image_size (INT)            max. Image size, default: 0
 *      image_link (STRING)         none, self or post (defalt: none)
 *      author_name (STRING)        last, initials or old, default: last
 *      editor_name (STRING)        last, initials or old, default: last
 *      author_separator (STRING)   The separator for author names
 *      editor_separator (STRING)   The separator for editor names
 *      style (STRING)              numbered, numbered_desc or none, default: none
 *      template (STRING)           the key of the template, default: tp_template_2016
 *      title_ref (STRING)          links or abstract, default: links
 *      link_style (STRING)         inline or images, default: inline
 *      date_format (STRING)        the format for date; needed for the types: presentations, online; default: d.m.Y
 *      pagination (INT)            activate pagination (1) or not (0), default: 1
 *      entries_per_page (INT)      number of publications per page (pagination must be set to 1), default: 30
 *      sort_list (STRING)          a list of publication types (separated by comma) which overwrites the default sort order for headline = 2 
 *      show_bibtex (INT)           0 (false) or 1 (true), default: 1
 *      container_suffix (STRING)   a suffix which can optionally set to modify container IDs in publication lists. It's not set by default.
 *      show_altmetric_donut (INT)  0 (false) or 1 (true), default: 0
 *      show_altmetric_entry (INT)  0 (false) or 1 (true), default: 0
 * 
 * @param array $atts
 * @return string
 * @since 0.12.0
*/
function tp_list_shortcode($atts){
    $atts = shortcode_atts(array(
       'user' => '',
       'tag' => '',
       'type' => '',
       'author' => '',
       'exclude' => '',
       'include' => '',
       'include_editor_as_author' => 1,
       'year' => '',
       'exclude_tags' => '',
       'exclude_types' => '',
       'order' => 'date DESC',
       'headline' => 1,
       'image' => 'none',
       'image_size' => 0,
       'image_link' => 'none',
       'author_name' => 'initials',
       'editor_name' => 'initials',
       'author_separator' => ';',
       'editor_separator' => ';',
       'style' => 'none',
       'template' => 'tp_template_2016',
       'title_ref' => 'links',
       'link_style' => 'inline',
       'date_format' => 'd.m.Y',
       'pagination' => 1,
       'entries_per_page' => 50,
       'sort_list' => '',
       'show_bibtex' => 1,
       'container_suffix' => '',
       'show_altmetric_donut' => 0,
       'show_altmetric_entry' => 0
    ), $atts);

    $tparray = array();
    $tpz = 0;
    $colspan = '';
    $headline = intval($atts['headline']);
    $image_size = intval($atts['image_size']);
    $pagination = intval($atts['pagination']);
    $entries_per_page = intval($atts['entries_per_page']);
    $sort_list = htmlspecialchars($atts['sort_list']);
    $form_limit = ( isset($_GET['limit']) ) ? intval($_GET['limit']) : '';

    $settings = array(
        'author_name' => htmlspecialchars($atts['author_name']),
        'editor_name' => htmlspecialchars($atts['editor_name']),
        'author_separator' => htmlspecialchars($atts['author_separator']),
        'editor_separator' => htmlspecialchars($atts['editor_separator']),
        'style' => htmlspecialchars($atts['style']),
        'template' => htmlspecialchars($atts['template']),
        'image' => htmlspecialchars($atts['image']),
        'image_link' => htmlspecialchars($atts['image_link']),
        'with_tags' => 0,
        'title_ref' => htmlspecialchars($atts['title_ref']),
        'link_style' => htmlspecialchars($atts['link_style']),
        'date_format' => htmlspecialchars($atts['date_format']),
        'convert_bibtex' => ( get_tp_option('convert_bibtex') == '1' ) ? true : false,
        'show_bibtex' => $atts['show_bibtex'] == '1' ? true : false,
        'container_suffix' => htmlspecialchars($atts['container_suffix']),
        'show_altmetric_entry' => $atts['show_altmetric_entry'] == '1' ? true : false,
        'show_altmetric_donut' => $atts['show_altmetric_donut'] == '1' ? true : false
    );
    
    // Handle limits for pagination
    $pagination_limits = tp_shortcodes::generate_pagination_limits($pagination, $entries_per_page, $form_limit );
    
    $page_link = ( get_option('permalink_structure') ) ? get_permalink() . "?" : get_permalink() . "&amp;";

    // Handle headline/order settings
    if ( $headline === 1 && strpos($atts['order'], 'year') === false && strpos($atts['order'], 'date') === false ) {
        $order = 'date DESC, ' . $atts['order'];
    }
    if ( $headline === 2 ) {
        $order = "type ASC, date DESC";
    }
    if ( $headline === 3 || $headline === 4  ) {
        $order = "year DESC , type ASC , date DESC ";
    }

    // Image settings
    if ( $settings['image']== 'left' || $settings['image']== 'right' ) {
       $settings['pad_size'] = $image_size + 5;
       $colspan = ' colspan="2"';
    }
    
    // Load template
    $template = tp_load_template($settings['template']);
    if ( $template === false ) {
        $template = tp_load_template('tp_template_orig');
    }
    
    // get publications
    $args = array(
        'tag' => $atts['tag'], 
        'year' => $atts['year'], 
        'type' => $atts['type'], 
        'author_id' => $atts['author'], 
        'user' => $atts['user'], 
        'order' => $atts['order'], 
        'exclude' => $atts['exclude'],
        'exclude_tags' => $atts['exclude_tags'],
        'exclude_types' => $atts['exclude_types'],
        'include' => $atts['include'], 
        'include_editor_as_author' => ($atts['include_editor_as_author'] == 1) ? true : false,
        'output_type' => ARRAY_A, 
        'limit' => $pagination_limits['limit']
    );
    $row = tp_publications::get_publications( $args );
    
    $number_entries = tp_publications::get_publications($args, true);
    $count = count($row);
    foreach ($row as $row) {
        $tparray[$tpz][0] = $row['year'];
        $number = tp_html_publication_template::prepare_publication_number($number_entries, $tpz, $pagination_limits['entry_limit'], $atts['style']);
        
        // teachPress style
        $tparray[$tpz][1] = tp_html_publication_template::get_single($row,'', $settings, $template, $number);
        
        if ( 2 <= $headline && $headline <= 4 ) {
                $tparray[$tpz][2] = $row['type'];
        }
        $tpz++;
    }
    
    // menu
    $r = '';
    $menu = ( $pagination === 1 ) ? tp_page_menu(array('number_entries' => $number_entries,
                                                       'entries_per_page' => $entries_per_page,
                                                       'current_page' => $pagination_limits['current_page'],
                                                       'entry_limit' => $pagination_limits['entry_limit'],
                                                       'page_link' => $page_link,
                                                       'link_attributes' => '',
                                                       'mode' => 'bottom',
                                                       'before' => '<div class="tablenav">',
                                                       'after' => '</div>')) : '';
    $r .= $menu;

    $row_year = ( $headline === 1 ) ? tp_publications::get_years( array('output_type' => ARRAY_A, 'order' => 'DESC') ) : '';
    
    $r .= tp_shortcodes::generate_pub_table(
                $tparray, 
                $template, 
                array( 'number_publications' => $tpz, 
                       'headline' => $headline,
                       'years' => $row_year,
                       'colspan' => $colspan,
                       'user' => $atts['user'],
                       'sort_list' => $sort_list ) );
    $r .= $menu;
    return $r;
}

/**
 * tpsearch: Frontend search function for publications
 *
 * possible values for $atts:
 *      user (STRING)               user_ids (separated by comma)
 *      tag (STRING)                tag_ids (separated by comma)
 *      entries_per_page (INT)      number of entries per page (default: 20)
 *      image (STRING)              none, left, right or bottom, default: none 
 *      image_size (INT)            max. Image size, default: 0
 *      image_link (STRING)         none, self or post (defalt: none)
 *      author_name (STRING)        last, initials or old, default: initials
 *      editor_name (STRING)        last, initials or old, default: initials
 *      author_separator (STRING)   The separator for author names, default: ;
 *      editor_separator (STRING)   The separator for editor names, default: ;
 *      style (STRING)              numbered, numbered_desc or none, default: none
 *      template (STRING)           the key of the template, default: tp_template_2016
 *      title_ref (STRING)          links or abstract, default: links
 *      link_style (STRING)         inline, images or direct, default: inline
 *      as_filter (STRING)          set it to "true" if you want to display publications by default
 *      date_format (STRING)        the format for date; needed for presentations, default: d.m.Y
 *      order (STRING)              date, title, year, bibtex or type, default: date DESC
 *      show_bibtex (INT)           0 (false) or 1 (true), default: 1
 *      container_suffix (STRING)   a suffix which can optionally set to modify container IDs in publication lists. It's not set by default.
 * 
 * @param array $atts
 * @return string
 * @since 4.0.0
 */
function tp_search_shortcode ($atts) {
    $atts = shortcode_atts(array(
       'user' => '',
       'tag' => '',
       'entries_per_page' => 20,
       'image' => 'none',
       'image_size' => 0,
       'image_link' => 'none',
       'author_name' => 'initials',
       'editor_name' => 'initials',
       'author_separator' => ';',
       'editor_separator' => ';',
       'style' => 'numbered',
       'template' => 'tp_template_orig_s',
       'title_ref' => 'links',
       'link_style' => 'inline',
       'as_filter' => 'false',
       'date_format' => 'd.m.Y',
       'order' => 'date DESC',
       'show_bibtex' => 1,
       'container_suffix' => ''
    ), $atts); 
    
    $tparray = array();
    $colspan = '';
    $image_size = intval($atts['image_size']);
    $entries_per_page = intval($atts['entries_per_page']);
    $order = esc_sql($atts['order']);
    $settings = array(
        'author_name' => htmlspecialchars($atts['author_name']),
        'editor_name' => htmlspecialchars($atts['editor_name']),
        'author_separator' => htmlspecialchars($atts['author_separator']),
        'editor_separator' => htmlspecialchars($atts['editor_separator']),
        'style' => htmlspecialchars($atts['style']),
        'template' => htmlspecialchars($atts['template']),
        'image' => htmlspecialchars($atts['image']),
        'image_link' => htmlspecialchars($atts['image_link']),
        'with_tags' => 0,
        'link_style' => htmlspecialchars($atts['link_style']),
        'title_ref' => htmlspecialchars($atts['title_ref']),
        'date_format' => htmlspecialchars($atts['date_format']),
        'convert_bibtex' => ( get_tp_option('convert_bibtex') == '1' ) ? true : false,
        'show_bibtex' => ( $atts['show_bibtex'] == '1' ) ? true : false,
        'show_altmetric_entry' => false,
        'show_altmetric_donut' => false,
        'container_suffix' => htmlspecialchars($atts['container_suffix'])
    );
    if ($settings['image']== 'left' || $settings['image']== 'right') {
       $settings['pad_size'] = $image_size + 5;
       $colspan = ' colspan="2"';
    }
    
    $search = isset( $_GET['tps'] ) ? htmlspecialchars( $_GET['tps'] ) : "";
    $link_attributes = "tps=$search";
    
    // Handle limits
    if ( isset( $_GET['limit'] ) ) {
        $current_page = intval( $_GET['limit'] );
        if ( $current_page <= 0 ) {
            $current_page = 1;
        }
        $entry_limit = ( $current_page - 1 ) * $entries_per_page;
    }
    else {
        $entry_limit = 0;
        $current_page = 1;
    }
    
    // Define pagelink
    $page_link = ( get_option('permalink_structure') ) ? get_permalink() . "?" : get_permalink() . "&amp;";
    
    $r = '';
    $r .= '<form method="get">';
    if ( !get_option('permalink_structure') ) {
        $r .= '<input type="hidden" name="p" id="page_id" value="' . get_the_id() . '"/>';
    }
    $r .= '<div class="tp_search_input">';
    
    if ( $search != "" ) {
        $r .= '<a name="tps_reset" class="tp_search_reset" title="' . __('Reset', 'teachpress') . '" href="' . get_permalink() . '">X</a>';
    }
    // If someone wants a form reset instead of a full reset:
    // $r .= '<a name="tps_reset" class="tp_search_reset" title="' . __('Reset', 'teachpress') . '" onclick="teachpress_tp_search_clean();">X</a>';
    
    $r .= '<input name="tps" id="tp_search_input_field" type="search" value="' . stripslashes($search) . '" tabindex="1" size="40"/>';
    $r .= '<input name="tps_button" class="tp_search_button" type="submit" value="' . __('Search', 'teachpress') . '"/>';
    
    $r .= '</div>';
    
    // If there is nothing to show
    if ( $search == "" && $atts['as_filter'] == 'false' ) {
        $r .= '</form>';
        return $r;
    }
    
    // get results
    $tpz = 0;
    $args = array ('user' => $atts['user'],
                   'tag' => $atts['tag'],
                   'search' => $search, 
                   'limit' => $entry_limit . ',' .  $entries_per_page,
                   'order' => $order,
                   'output_type' => ARRAY_A);
    $results = tp_publications::get_publications( $args );
    $number_entries = tp_publications::get_publications($args, true);

    // menu
    $menu = tp_page_menu(array('number_entries' => $number_entries,
                               'entries_per_page' => $entries_per_page,
                               'current_page' => $current_page,
                               'entry_limit' => $entry_limit,
                               'page_link' => $page_link,
                               'link_attributes' => $link_attributes,
                               'mode' => 'bottom',
                               'before' => '<div class="tablenav">',
                               'after' => '</div>'));
    if ( $search != "" ) {
        $r .= '<h3 class="tp_search_result">' . __('Results for','teachpress') . ' "' . stripslashes($search) . '":</h3>';
    }
    $r .= $menu;

    // Load template
    $template = tp_load_template($settings['template']);
    if ( $template === false ) {
        $template = tp_load_template('tp_template_orig_s');
    }

    // If there are no results
    if ( count($results) === 0 ) {
        $r .= '<div class="teachpress_message_error">' . __('Sorry, no entries matched your criteria.','teachpress') . '</div>';
    }
    // Show results
    else {
        foreach ($results as $row) {
            $count = tp_html_publication_template::prepare_publication_number($number_entries, $tpz, $entry_limit, $atts['style']);
            $tparray[$tpz][0] = $row['year'];
            $tparray[$tpz][1] = tp_html_publication_template::get_single($row,'', $settings, $template, $count);
            $tpz++;
        }
        $r .= tp_shortcodes::generate_pub_table(
                    $tparray, 
                    $template, 
                    array( 'number_publications' => $tpz, 
                           'colspan' => $colspan,
                           'headline' => 0,
                           'user' => '') );
    }
    $r .= $menu;
    
    $r .= '</form>';
    return $r;
}

/** 
 * Private Post shortcode
 * 
 * possible values for atts:
 *      id (INT)        The id of the course
 * 
 * @param array $atts       The parameter array (key: id)
 * @param string $content   The content you want to display
 * @return string
 * @since 2.0.0
*/
function tp_post_shortcode ($atts, $content) {
    $param = shortcode_atts(array('id' => 0), $atts);
    $id = intval($param['id']);
    $test = tp_courses::is_student_subscribed($id, true);
    if ( $test === true ) {
        return $content;
    }
}
