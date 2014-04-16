<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LI_Pagination extends CI_Pagination {

	/*Extend Property*/
	var $next_pape_num 			= 10;
	var $next_num_link			= '下10页';
	var $prev_num_link			= '上10页';
	var $next_num_tag_open		= '';
	var $prev_num_tag_open		= '';
	var $next_num_tag_close		= '';
	var $prev_num_tag_close		= '';
	var $num_tag_open			= '';
	var $first_tag_close		= '';
	var $last_tag_open			= '';
	var $next_link				= FALSE;
	var $prev_link				= FALSE;
	var $ajax_func              = 'ajax_page';
	var $common_func			= 'if(typeof Common == "function"){Common.pagination(this);}';
	
	var $per_page				= Constant::DEFAULT_PER_PAGE;
	var $page_limit				= Constant::DEFAULT_PAGE_LIMIT;
	var $uri_segment			= 4;
	var $cur_tag_open			= '<a href="javascript:void(0);" class="active">';
	var $cur_tag_close			= '</a>';
	var $full_tag_open			= '<div class="pagination fr">';
	var $full_tag_close			= '</div>';	
	var $first_link				= '首页';
	var $last_link				= FALSE;
	var $first_url				= '1';
	var $use_page_numbers		= TRUE;
	var $page_query_string		= TRUE;
	var $query_string_segment	= 'page';
	var $num_links				= 5;	
	var $params 				= array();//额外参数：e.g. $config['params'] = array('"123"','1');
	var $default_page			= 1;
	var $uri_seperator			= '/';

	function __construct()
	{
		parent::__construct();
	}
	
	function create_ajax_links()
    {
    	//page_limit
    	if($this->page_limit && $this->total_rows > $this->page_limit * $this->per_page) $this->total_rows = $this->page_limit * $this->per_page;

    	//add common function
    	$this->ajax_func = $this->common_func.$this->ajax_func;

    	//add additional params
    	if(!empty($this->params)) $additional_params = ','.implode(',',$this->params);
    	else $additional_params = '';

        // If our item count or per-page total is zero there is no need to continue.  
        if ($this->total_rows == 0 OR $this->per_page == 0)  
        {  
            return '';  
        }  
  
        // Calculate the total number of pages  
        $num_pages = ceil($this->total_rows / $this->per_page);

		/************Extend code Begin**********************/
		$is_show_next_num_link = $is_show_prev_num_link = FALSE;
		$go_next_num = $num_pages - ((int) $this->cur_page + $this->next_pape_num);

		$go_prev_num = (int) $this->cur_page - $this->next_pape_num;
		if($go_next_num >= 0)
		{
			$is_show_next_num_link = TRUE;
		}
		if($go_prev_num > 0 )
		{
			$is_show_prev_num_link = TRUE;
		}
		/************Extend code END**********************/
  
        // Is there only one page? Hm... nothing more to do here then.  
        if ($num_pages == 1)  
        {  
            return '';  
        }  
  
        // Set the base page index for starting page number  
        if ($this->use_page_numbers)  
        {  
            $base_page = 1;  
        }  
        else  
        {  
            $base_page = 0;  
        }  
  
        // Determine the current page number.  
        $CI =& get_instance();  
  
        if ($CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE)  
        {  
            if ($CI->input->get($this->query_string_segment) != $base_page)  

            { 
                $this->cur_page = $CI->input->get($this->query_string_segment);  

                // Prep the current page - no funny business!  
                $this->cur_page = (int) $this->cur_page;  
            }  
        }  
        else  
        {  
            if ($CI->uri->segment($this->uri_segment) != $base_page)  
            {  
                $this->cur_page = $CI->uri->segment($this->uri_segment);  
  
                // Prep the current page - no funny business!  
                $this->cur_page = (int) $this->cur_page;  
            }  
        }  

        // Set current page to 1 if using page numbers instead of offset  
        if ($this->use_page_numbers AND $this->cur_page == 0)  
        {  
            $this->cur_page = $base_page;  
        }  
 
        $this->num_links = (int)$this->num_links;  
  
        if ($this->num_links < 1)  
        {  
            show_error('Your number of links must be a positive number.');  
        }  
  
        if ( ! is_numeric($this->cur_page))  
        {  
            $this->cur_page = $base_page;  
        }  
  
        // Is the page number beyond the result range?  
        // If so we show the last page  
        if ($this->use_page_numbers)  
        {  
            if ($this->cur_page > $num_pages)  
            {  
                $this->cur_page = $num_pages;  
            }  
        }  
        else  
        {  
            if ($this->cur_page > $this->total_rows)  
            {  
                $this->cur_page = ($num_pages - 1) * $this->per_page;  
            }  
        }  
  
        $uri_page_number = $this->cur_page;  
          
        if ( ! $this->use_page_numbers)  
        {  
            $this->cur_page = floor(($this->cur_page/$this->per_page) + 1);  
        }  
  
        // Calculate the start and end numbers. These determine  
        // which number to start and end the digit links with  
        $start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page - ($this->num_links - 1) : 1;  
        $end   = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;  
		
        // Is pagination being used over GET or POST?  If get, add a per_page query  
        // string. If post, add a trailing slash to the base URL if needed  
        if ($CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE)  
        {  
            $this->base_url = rtrim($this->base_url).'&'.$this->query_string_segment.'=';  
        }  
        else  
        {  
            $this->base_url = rtrim($this->base_url, '/') .'/';  
        }  
  
        // And here we go...  
        $output = '';  
  
        // Render the "First" link  
        if  ($this->first_link !== FALSE AND $this->cur_page > ($this->num_links + 1))  
        {  
            $first_url = ($this->first_url == '') ? $this->base_url : $this->first_url;  
            $output .= $this->first_tag_open.'<a '." onclick='".$this->ajax_func."(0{$additional_params});return false;'".$this->anchor_class.'href="javascript:void(0)">'.$this->first_link.'</a>'.$this->first_tag_close;  
        }  

        // Render the "previous" link  
        if  ($this->prev_link !== FALSE AND $this->cur_page != 1)  
        {  
            if ($this->use_page_numbers)  
            {  
                $i = $uri_page_number - 1;  
            }  
            else  
            {  
                $i = $uri_page_number - $this->per_page;  
            }  
  
            if ($i == 0 && $this->first_url != '')  
            {  
                $output .= $this->prev_tag_open.'<a '." onclick='".$this->ajax_func."(0{$additional_params});return false;'".$this->anchor_class.'href="javascript:void(0)">'.$this->prev_link.'</a>'.$this->prev_tag_close;  
            }  
            else  
            {  
                $i = ($i == 0) ? $this->default_page : $this->prefix.$i.$this->suffix;  
                $output .= $this->prev_tag_open.'<a '." onclick='".$this->ajax_func."({$i}{$additional_params});return false;'".$this->anchor_class.'href="javascript:void(0)">'.$this->prev_link.'</a>'.$this->prev_tag_close;  
            }  
  
        }  
		
		/************Extend code Begin**********************/
		if	( $this->prev_num_link == TRUE AND $is_show_prev_num_link == TRUE AND $this->cur_page != 1)
		{
			if ($this->use_page_numbers)
			{
				$i = $uri_page_number - $this->next_pape_num;
			}
			else
			{
				$i = $uri_page_number - $this->per_page * $this->next_pape_num;
			}
			
			
			if ($i == 0 && $this->first_url != '')  
            {  
                $output .= $this->prev_num_tag_open.'<a '." onclick='".$this->ajax_func."(0{$additional_params});return false;'".$this->anchor_class.'href="javascript:void(0)">'.$this->prev_num_link.'</a>'.$this->prev_num_tag_close;  
            }  
            else  
            {  
                $i = ($i == 0) ? $this->default_page : $this->prefix.$i.$this->suffix;
                $output .= $this->prev_num_tag_open.'<a '." onclick='".$this->ajax_func."({$i}{$additional_params});return false;'".$this->anchor_class.'href="javascript:void(0)">'.$this->prev_num_link.'</a>'.$this->prev_num_tag_close;  
            }  
			
		}
		/************Extend code end**********************/
  
        // Render the pages  
        if ($this->display_pages !== FALSE)  
        {  
            // Write the digit links  
            for ($loop = $start -1; $loop <= $end; $loop++)  
            {  
                if ($this->use_page_numbers)  
                {  
                    $i = $loop;  
                }  
                else  
                {  
                    $i = ($loop * $this->per_page) - $this->per_page;  
                }  
  
                if ($i >= $base_page)  
                {  
                    if ($this->cur_page == $loop)  
                    {  
                        $output .= $this->cur_tag_open.$loop.$this->cur_tag_close; // Current page  
                    }  
                    else  
                    {  
                        $n = ($i == $base_page) ? $this->default_page : $i;  
  
                        if ($n == '' && $this->first_url != '')  
                        {  

                            $output .= $this->num_tag_open.'<a '." onclick='".$this->ajax_func."(0{$additional_params});return false;'".$this->anchor_class.'href="javascript:void(0);">'.$loop.'</a>'.$this->num_tag_close;

                        }  
                        else  
                        {  
                            $n = ($n == '') ? $this->default_page : $this->prefix.$n.$this->suffix;  

                            $output .= $this->num_tag_open.'<a '." onclick='".$this->ajax_func."({$n}{$additional_params});return false;'".$this->anchor_class.'href="javascript:void(0);">'.$loop.'</a>'.$this->num_tag_close;  

                        }  
                    }  
                }  
            }  
        }  
  
        // Render the "next" link  
        if ($this->next_link !== FALSE AND $this->cur_page < $num_pages)  
        {  
            if ($this->use_page_numbers)  
            {  
                $i = $this->cur_page + 1;  
            }  
            else  
            {  
                $i = ($this->cur_page * $this->per_page);  
            }  
            $ajax_p = $this->prefix.$i.$this->suffix;  

            $output .= $this->next_tag_open.'<a '." onclick='".$this->ajax_func."({$ajax_p}{$additional_params});return false;'".$this->anchor_class.'href="javascript:void(0)">'.$this->next_link.'</a>'.$this->next_tag_close;  

        }  
		
		/************Extend code Begin**********************/
		if	( $this->next_num_link == TRUE AND $is_show_next_num_link == TRUE /*AND $this->cur_page != 1*/)
		{
			if ($this->use_page_numbers)
			{
				$i = $uri_page_number + $this->next_pape_num;
				
			}
			else
			{
				$i = $uri_page_number + $this->per_page * $this->next_pape_num;
			}
			$ajax_p = $this->prefix.$i.$this->suffix;  

            $output .= $this->next_num_tag_open.'<a '." onclick='".$this->ajax_func."({$ajax_p}{$additional_params});return false;'".$this->anchor_class.'href="javascript:void(0)">'.$this->next_num_link.'</a>'.$this->next_num_tag_close;  

		}
	/************Extend code end**********************/
  
        // Render the "Last" link  
        if ($this->last_link !== FALSE AND ($this->cur_page + $this->num_links) < $num_pages)  
        {  
            if ($this->use_page_numbers)  
            {  
                $i = $num_pages;  
            }  
            else  
            {  
                $i = (($num_pages * $this->per_page) - $this->per_page);  
            }  
            $ajax_p = $this->prefix.$i.$this->suffix;  

            $output .= $this->last_tag_open.'<a '." onclick='".$this->ajax_func."({$ajax_p}{$additional_params});'". $this->anchor_class.'href="javascript:void(0)">'.$this->last_link.'</a>'.$this->last_tag_close;  

        }  
  
        // Kill double slashes.  Note: Sometimes we can end up with a double slash  
        // in the penultimate link so we'll kill all double slashes.  
        $output = preg_replace("#([^:])//+#", "\\1/", $output);  
  
        // Add the wrapper HTML if exists  
        $output = $this->full_tag_open.$output.$this->full_tag_close;  
  
        return $output;  
    }  
	


	function create_links()
	{
		// If our item count or per-page total is zero there is no need to continue.
		if ($this->total_rows == 0 OR $this->per_page == 0)
		{
			return '';
		}
		
		
		// Calculate the total number of pages
		$num_pages = ceil($this->total_rows / $this->per_page);
		
		/************Extend code Begin**********************/
		$is_show_next_num_link = $is_show_prev_num_link = FALSE;
		$go_next_num = $num_pages - ((int) $this->cur_page + $this->next_pape_num);
		$go_prev_num = (int) $this->cur_page - $this->next_pape_num;
		if($go_next_num >= 0)
		{
			$is_show_next_num_link = TRUE;
		}
		if($go_prev_num > 0 )
		{
			$is_show_prev_num_link = TRUE;
		}
		/************Extend code END**********************/
		// Is there only one page? Hm... nothing more to do here then.
		if ($num_pages == 1)
		{
			return '';
		}

		// Set the base page index for starting page number
		if ($this->use_page_numbers)
		{
			$base_page = 1;
		}
		else
		{
			$base_page = 0;
		}

		// Determine the current page number.
		$CI =& get_instance();

		if ($CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE)
		{
			if ($CI->input->get($this->query_string_segment) != $base_page)
			{
				$this->cur_page = $CI->input->get($this->query_string_segment);

				// Prep the current page - no funny business!
				$this->cur_page = (int) $this->cur_page;
			}
		}
		else
		{
			//tizi segment
			$segment = $CI->uri->segment_array();

			if($this->uri_seperator != '/')
			{
				$segment = $this->uri_seperator.ltrim(rtrim($CI->uri->uri_string(),'/'),'/');
				$segment = explode($this->uri_seperator, $segment);
			}
			
			$segment_page = isset($segment[$this->uri_segment])?$segment[$this->uri_segment]:'';

			if (empty($segment_page))
			{
				$this->cur_page =1;
			}
			else if ($segment_page != $base_page)
			{
				if(is_null($segment_page))
				{
					$this->cur_page =1;
				}
				else
				{
					$this->cur_page = $segment_page;
				}
				// Prep the current page - no funny business!
				$this->cur_page = (int) $this->cur_page;
			}
		}
		
		// Set current page to 1 if using page numbers instead of offset
		if ($this->use_page_numbers AND $this->cur_page == 0)
		{
			$this->cur_page = $base_page;
		}

		$this->num_links = (int)$this->num_links;

		if ($this->num_links < 1)
		{
			show_error('Your number of links must be a positive number.');
		}

		if ( ! is_numeric($this->cur_page))
		{
			$this->cur_page = $base_page;
		}

		// Is the page number beyond the result range?
		// If so we show the last page
		if ($this->use_page_numbers)
		{
			if ($this->cur_page > $num_pages)
			{
				$this->cur_page = $num_pages;
			}
		}
		else
		{
			if ($this->cur_page > $this->total_rows)
			{
				$this->cur_page = ($num_pages - 1) * $this->per_page;
			}
		}

		$uri_page_number = $this->cur_page;
		
		if ( ! $this->use_page_numbers)
		{
			$this->cur_page = floor(($this->cur_page/$this->per_page) + 1);
		}

		// Calculate the start and end numbers. These determine
		// which number to start and end the digit links with
		$start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page - ($this->num_links - 1) : 1;
		$end   = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;

		// Is pagination being used over GET or POST?  If get, add a per_page query
		// string. If post, add a trailing slash to the base URL if needed
		if ($CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE)
		{
			$this->base_url = rtrim($this->base_url).($this->uri_segment?'?':'&amp;').$this->query_string_segment.'=';
		}
		else
		{
			//$this->base_url = rtrim($this->base_url, '/') .'/';
			$this->base_url = rtrim($this->base_url, '/') . $this->uri_seperator;
		}

		// And here we go...
		$output = '';

		// Render the "First" link
		if  ($this->first_link !== FALSE AND $this->cur_page > ($this->num_links + 1))
		{
			$first_url = ($this->first_url == '') ? $this->base_url : $this->first_url;
			$output .= $this->first_tag_open.'<a '.$this->anchor_class.'href="'.$first_url.'">'.$this->first_link.'</a>'.$this->first_tag_close;
		}
		/************Extend code Begin**********************/
		if	( $this->prev_num_link == TRUE AND $is_show_prev_num_link == TRUE AND $this->cur_page != 1)
		{
			if ($this->use_page_numbers)
			{
				$i = $uri_page_number - $this->next_pape_num;
			}
			else
			{
				$i = $uri_page_number - $this->per_page * $this->next_pape_num;
			}

			if ($i == 0 && $this->first_url != '')
			{
				$output .= $this->prev_num_tag_open.'<a '.$this->anchor_class.'href="'.$this->first_url.'">'.$this->prev_num_link.'</a>'.$this->prev_num_tag_close;
			}
			else
			{
				$i = ($i == 0) ? $this->default_page : $this->prefix.$i.$this->suffix;
				$output .= $this->prev_num_tag_open.'<a '.$this->anchor_class.'href="'.$this->base_url.$i.'">'.$this->prev_num_link.'</a>'.$this->prev_num_tag_close;
			}
		}
		/************Extend code end**********************/
		// Render the "previous" link
		if  ($this->prev_link !== FALSE AND $this->cur_page != 1)
		{
			if ($this->use_page_numbers)
			{
				$i = $uri_page_number - 1;
			}
			else
			{
				$i = $uri_page_number - $this->per_page;
			}

			if ($i == 0 && $this->first_url != '')
			{
				$output .= $this->prev_tag_open.'<a '.$this->anchor_class.'href="'.$this->first_url.'">'.$this->prev_link.'</a>'.$this->prev_tag_close;
			}
			else
			{
				$i = ($i == 0) ? $this->default_page : $this->prefix.$i.$this->suffix;
				$output .= $this->prev_tag_open.'<a '.$this->anchor_class.'href="'.$this->base_url.$i.'">'.$this->prev_link.'</a>'.$this->prev_tag_close;
			}

		}

		// Render the pages
		if ($this->display_pages !== FALSE)
		{
			// Write the digit links
			for ($loop = $start -1; $loop <= $end; $loop++)
			{
				if ($this->use_page_numbers)
				{
					$i = $loop;
				}
				else
				{
					$i = ($loop * $this->per_page) - $this->per_page;
				}

				if ($i >= $base_page)
				{
					if ($this->cur_page == $loop)
					{
						$output .= $this->cur_tag_open.$loop.$this->cur_tag_close; // Current page
					}
					else
					{
						$n = ($i == $base_page) ? $this->default_page : $i;

						if ($n == '' && $this->first_url != '')
						{
							$output .= $this->num_tag_open.'<a '.$this->anchor_class.'href="'.$this->first_url.'">'.$loop.'</a>'.$this->num_tag_close;
						}
						else
						{
							$n = ($n == '') ? $this->default_page : $this->prefix.$n.$this->suffix;

							$output .= $this->num_tag_open.'<a '.$this->anchor_class.'href="'.$this->base_url.$n.'">'.$loop.'</a>'.$this->num_tag_close;
						}
					}
				}
			}
		}

		// Render the "next" link
		if ($this->next_link !== FALSE AND $this->cur_page < $num_pages)
		{
			if ($this->use_page_numbers)
			{
				$i = $this->cur_page + 1;
			}
			else
			{
				$i = ($this->cur_page * $this->per_page);
			}

			$output .= $this->next_tag_open.'<a '.$this->anchor_class.'href="'.$this->base_url.$this->prefix.$i.$this->suffix.'">'.$this->next_link.'</a>'.$this->next_tag_close;
		}
		
		/************Extend code Begin**********************/
		if	( $this->next_num_link == TRUE AND $is_show_next_num_link == TRUE /*AND $this->cur_page != 1*/)
		{
			if ($this->use_page_numbers)
			{
				$i = $uri_page_number + $this->next_pape_num;
				
			}
			else
			{
				$i = $uri_page_number + $this->per_page * $this->next_pape_num;
			}

			if ($i == 0 && $this->first_url != '')
			{
				$output .= $this->next_num_tag_open.'<a '.$this->anchor_class.'href="'.$this->first_url.'">'.$this->next_num_link.'</a>'.$this->next_num_tag_close;
			}
			else
			{
				$i = ($i == 0) ? $this->default_page : $this->prefix.$i.$this->suffix;
				$output .= $this->next_num_tag_open.'<a '.$this->anchor_class.'href="'.$this->base_url.$i.'">'.$this->next_num_link.'</a>'.$this->next_num_tag_close;
			}
		}
	/************Extend code end**********************/
		// Render the "Last" link
		if ($this->last_link !== FALSE AND ($this->cur_page + $this->num_links) < $num_pages)
		{
			if ($this->use_page_numbers)
			{
				$i = $num_pages;
			}
			else
			{
				$i = (($num_pages * $this->per_page) - $this->per_page);
			}
			$output .= $this->last_tag_open.'<a '.$this->anchor_class.'href="'.$this->base_url.$this->prefix.$i.$this->suffix.'">'.$this->last_link.'</a>'.$this->last_tag_close;
		}

		// Kill double slashes.  Note: Sometimes we can end up with a double slash
		// in the penultimate link so we'll kill all double slashes.
		$output = preg_replace("#([^:])//+#", "\\1/", $output);

		// Add the wrapper HTML if exists
		$output = $this->full_tag_open.$output.$this->full_tag_close;

		return $output;
	}
}

