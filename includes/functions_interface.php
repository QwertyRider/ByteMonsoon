<?php
/*
ByteMonsoon 2.1.1
http://www.sourceforge.net/projects/bytemonsoon
bytemonsoon@saevian.com
*/
require_once('config.php');

function getmicrotime() { 
    list($usec, $sec) = explode(' ',microtime()); 
    return ((float)$usec + (float)$sec); 
} 

function dbconn() {
	if (MYSQL_PERSISTANT)
	mysql_pconnect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS) or die(mysql_error());
	else
	mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS) or die(mysql_error());
	
	mysql_select_db(MYSQL_DB) or die(mysql_error());
}

function make_size($bytes) {
   $suffix = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
   $index = floor(log($bytes + 1, 1024));
   return sprintf('%.0f %s', $bytes / pow (1024, $index), $suffix[$index]);
}

function make_share_ratio_color($x) {
	if ($x < 0.800)
		return 'red';
	elseif ($x < 1.200)
		return 'yellow';
	else
		return 'green';
}

// Is this as efficient and fast as it could be?
function make_time($s) {
	if ($s < 0)
		$s = 0;
	$t = array();
	foreach (array('60:sec','60:min','24:hour','0:day') as $x) {
		$y = explode(':', $x);
		if ($y[0] > 1) {
			$v = $s % $y[0];
			$s = floor($s / $y[0]);
		}
		else
			$v = $s;
		$t[$y[1]] = $v;
	}

	if ($t['day'])
		return $t['day'] . ' day(s), ' . sprintf('%02d:%02d:%02d', $t['hour'], $t['min'], $t['sec']);
	if ($t['hour'])
		return sprintf('%d:%02d:%02d', $t['hour'], $t['min'], $t['sec']);
	if ($t['min'])
		return sprintf('%d:%02d', $t['min'], $t['sec']);
	return $t['sec'] . ' secs';
}

// Split up into multiple pages depending on setting in configuration.
function make_pages() {


}

/*
Template system based on PHEMPLATE v1.10.1
Original Author: pukomuko <salna@ktl.mii.lt>
Website: http://pukomuko.esu.lt
*/
define('TPL_LOOP', 1);
define('TPL_NOLOOP', 2);
define('TPL_INCLUDE', 4);
define('TPL_APPEND', 8);
define('TPL_FINISH', 16);
define('TPL_OPTIONAL', 32);
define('TPL_LOOP_INNER_PARSED', 64);
define('TPL_LOOP_INNER_OPTIONAL', 128);

define('TPL_PARSEDLOOP', TPL_LOOP | TPL_LOOP_INNER_PARSED);
define('TPL_OPTLOOP',    TPL_PARSEDLOOP | TPL_LOOP_INNER_OPTIONAL);

define('TPL_BLOCK',        1);
define('TPL_BLOCKREC',    2);
define('TPL_STRIP_UTF_HEADER',    4);

class phemplate
{
    var $vars = array();
    var $loops = array();
    var $root = '';
    var $unknowns = 'keep';
    var $parameters = 0;
    var $error_handler = null;
    var $block_start_string = '<block="|">';
    var $block_end_string = '</block="|">';
    function phemplate( $root_dir = '', $unknowns = 'keep', $params = 0)
    {
        $this->set_root($root_dir);
        $this->set_unknowns($unknowns);
        $this->set_params($params);
    }

    function set_root($root)
    {
        if (empty($root)) return;
        if (!is_dir($root))
        {
            $this->error("phemplate::set_root(): $root is not a directory.", 'warning');
            return false;
        }
        
        $this->root = $root;
        return true;
    }
	
    function set_unknowns($unk)
    {
        $this->unknowns = $unk;
    }

    function set_params($params)
    {
        $this->parameters = $params;
    }

    function set_file($handle, $filename = "", $blocks = false)
    {
            if ($filename == "")
            {
                $this->error("phemplate::set_file(): filename for handle '$handle' is empty.", 'fatal');
                return false;
            }
            $this->vars[$handle] = $this->read_file($filename);
            if ($blocks & TPL_STRIP_UTF_HEADER)
            {
                $header = substr($this->vars[$handle], 0, 3);
                if ("\xEF\xBB\xBF" == $header) $this->vars[$handle] = substr($this->vars[$handle], 3);
            }
            if ($blocks) { $this->extract_blocks($handle, $blocks & TPL_BLOCKREC); }
            return true;

    }

    function set_var($var_name, $var_value)
    {
        if (is_array($var_value))
        {
            foreach($var_value as $key=>$value)
            {
                $this->set_var($var_name . '.' . $key, $value);
            }
        }
        else
        {
            $this->vars[$var_name] = $var_value;
        }
    }

    function tie_var($var_name, &$var_value)
    {
        if (is_array($var_value))
        {
            $list = array_keys($var_value);
            foreach($list as $key)
            {
                $this->tie_var($var_name . '.' . $key, $var_value[$key]);
            }
        }
        else
        {
            $this->vars[$var_name] =& $var_value;
        }
    }

    function get_var($handle)
    {
        if (!isset($this->vars[$handle])) { $this->error("phemplate(): no such handle '$handle'", 'warning'); }
        return $this->vars[$handle];
    }

    function get_var_silent($handle)
    {
        if (!isset($this->vars[$handle])) { $this->vars[$handle] = ''; return ''; }
        return $this->vars[$handle];
    }

    function set_loop($loop_name, $loop)
    {
        if (!$loop) $loop = 0;
        $this->loops[$loop_name] = $loop;
    }

    function tie_loop($loop_name, &$loop)
    {
        if (!$loop) $loop = 0;
        $this->loops[$loop_name] =& $loop;
    }

    function extract_blocks($bl_handle, $recurse = false)
    {

        $str = $this->get_var($bl_handle);
        if (!$str) return $str;
        $bl_start = 0;

        list($bll, $blr) = explode('|', $this->block_start_string);
        $strlen = strlen($bll);

        while(is_long($bl_start = strpos($str, $bll, $bl_start)))
        {
            $pos = $bl_start + $strlen;

            $endpos = strpos($str, $blr, $pos);
            $handle = substr($str, $pos, $endpos-$pos);

            $tag = $bll.$handle.$blr;
            $endtag = str_replace('|', $handle, $this->block_end_string);

            $start_pos = $bl_start + strlen($tag);
            $end_pos = strpos($str, $endtag, $bl_start);
            if (!$end_pos) { $this->error("phemplate(): block '$handle' has no ending tag", 'fatal'); }
            $bl_end = $end_pos + strlen($endtag);

            $block_code = substr($str, $start_pos, $end_pos-$start_pos);
            
            $this->set_var($handle, $block_code);

            $part1 = substr($str, 0, $bl_start);
            $part2 = substr($str, $bl_end, strlen($str));
            
            $str = $part1 . $part2;

            if ($recurse) { $this->extract_blocks($handle, 1); }

        }

        $this->set_var($bl_handle, $str);

    }
	
    function include_files($handle)
    {
        $str = $this->get_var($handle);

        while(is_long($pos = strpos($str, '<include filename="')))
        {
            $pos += 19;
            $endpos = strpos($str, '">', $pos);
            $filename = substr($str, $pos, $endpos-$pos);
            $tag = '<include filename="'.$filename.'">';

            
            $include = $this->read_file($filename);

            $str = str_replace($tag, $include, $str);
        }

        return $str;
    }

    function parse_loops($handle, $noloop = false)
    {
        $str = $this->get_var($handle);
        
        reset($this->loops);

        while ( list($loop_name, $loop_ar) = each($this->loops) )
        {

            $start_tag = strpos($str, '<loop="'.$loop_name.'">');

            $start_pos = $start_tag + strlen('<loop="'.$loop_name.'">');
            if (!$start_pos) continue;
            $end_pos = strpos($str, '</loop="'.$loop_name.'">');

            $loop_code = substr($str, $start_pos, $end_pos-$start_pos);
            $org_loop_code = $loop_code;
            
            $start_tag = substr($str, $start_tag, strlen('<loop="'.$loop_name.'">'));
            $end_tag = substr($str, $end_pos, strlen('</loop="'.$loop_name.'">'));

            if($loop_code != ''){
                    
                    $new_code = '';

                    if ($noloop & TPL_NOLOOP)
                    {
                        
                        $nl_start_tag = strpos($loop_code, '<noloop name="'.$loop_name.'">');
                        $nl_start_pos = $nl_start_tag + strlen('<noloop name="'.$loop_name.'">');

                        if ($nl_start_pos)
                        {
                            
                            $nl_end_pos = strpos($loop_code, '</noloop name="'.$loop_name.'">');

                            $noloop_code = substr($loop_code, $nl_start_pos, $nl_end_pos - $nl_start_pos);
    
                            
                            $nl_start_tag = substr($loop_code, $nl_start_tag, strlen('<noloop name="'.$loop_name.'">'));
                            $nl_end_tag = substr($loop_code, $nl_end_pos, strlen('</noloop name="'.$loop_name.'">'));
                            $loop_code = str_replace($nl_start_tag.$noloop_code.$nl_end_tag, '', $loop_code);
                        }

                    }

                    if (is_array($loop_ar))
                    {

                        if ($noloop & TPL_LOOP_INNER_PARSED)
                        {
                            for($i = 0; isset($loop_ar[$i]); $i++)

                            {
                                $temp_code = $loop_code;

                                $array_keys = array_keys($loop_ar[$i]);
                                $this->set_var($loop_name, $loop_ar[$i]);
                                if ($noloop & TPL_LOOP_INNER_OPTIONAL) $temp_code = $this->optional($loop_code);
                                $temp_code = $this->parse($temp_code);
                                $new_code .= $temp_code;

                                foreach ($array_keys as $key) unset($this->vars[$loop_name.'.'.$key]);

                            }
                        }
                        else
                        {
                            $ar_keys = array_keys($loop_ar);
                            $ar_size = count($ar_keys);
                            for($i = 0; ($i< $ar_size); $i++)
                            {
                                $temp_code = $loop_code;

                                foreach( $loop_ar[$ar_keys[$i]] as $k=>$v)
                                {
                                    $temp_code = str_replace( '{'. $loop_name. '.' .$k. '}', $v, $temp_code);
                                }
                                $new_code .= $temp_code;
                            }
                        }
                    } elseif ($noloop & TPL_NOLOOP)
                    {
                            $new_code = $noloop_code;
                    }


                    $str = str_replace($start_tag.$org_loop_code.$end_tag, $new_code, $str);
            }
            
        }
    
        return $str;
    }
    
    function parse($string)
    {

        $str = explode('{', $string);

        $res = '';

        for ($i = 0; isset($str[$i]); $i++)
        {
            if ($i === 0)
            {
                $res .= $str[$i];
            }
            else
            {
                $line = explode('}', $str[$i]);
                $key = $line[0];
                unset($line[0]);

                if ( $key && isset($this->vars[$key]) )
                {
                    $res .= $this->vars[$key].implode('}', $line);
                }
                else
                {
                    switch ($this->unknowns)
                    {
                        case "keep":
                            $res .= '{'.$key;
                            if (count ($line) >    0)
                            {
                                $res .= '}';
                                $res .= implode('}', $line);
                            }
                        break;

                        case "remove":
                                $res .= implode('', $line);
                        break;

                        case "remove_nonjs":
                                if (!empty($key) && ((false === strpos($key, ' ')) && (false === strpos($key, "\n")) && (false === strpos($key, "\t"))))
                                {
                                    $res .= implode('}', $line);
                                }
                                else
                                {
                                    $res .= '{'.$key;
                                    if (count ($line) >    0)
                                    {
                                        $res .= '}';
                                        $res .= implode('}', $line);
                                    }
                                }
                        break;

                        case "comment":
                                $res .= '<!-- '.$key.' -->'.implode('', $line);
                        break;
                        
                        case "space":
                                $res .= '&nbsp;'.implode('', $line);
                        break;

                    }
                }
            }
        }

        return $res;
    }

    function finish($str)
    {
        switch ($this->unknowns)
        {
            case "keep":
            break;

            case "remove":
            $str = preg_replace('/{[^ \t\r\n}]+}/', "", $str);
            break;

            case "comment":
            $str = preg_replace('/{([^ \t\r\n}]+)}/', "<!-- {\\1} -->", $str);
            break;
            
            case "space":
            $str = preg_replace('/{([^ \t\r\n}]+)}/', "&nbsp;", $str);
            break;

        }

        return $str;
    }

    function optional($str)
    {
        $bl_start = 0;

        while(is_long($bl_start = strpos($str, '<opt name="', $bl_start)))
        {
            $pos = $bl_start + 11;

            $endpos = strpos($str, '">', $pos);
            $varname = substr($str, $pos, $endpos-$pos);
            
            $tag = '<opt name="'.$varname.'">';
            $endtag = '</opt name="'.$varname.'">';

            $end_pos = strpos($str, $endtag, $bl_start);
            if (!$end_pos) { $this->error("phemplate(): optional '$varname' has no ending tag", 'fatal'); }

            $bl_end = $end_pos + strlen($endtag);


            $part1 = substr($str, 0, $bl_start);
            $part2 = substr($str, $bl_end, strlen($str));
            
            $value = $this->get_var_silent($varname);

            if ($value || $value === 0 || $value === '0')
            {
                $start_pos = $bl_start + strlen($tag);

                $block_code = substr($str, $start_pos, $end_pos-$start_pos);
                
                $str = $part1 . $this->parse($block_code) . $part2;
            }
            else
            {
                $str = $part1 . $part2;
            }

        }

        return $str;
    }

    function process($target, $handle, $loop = false, $include = false, $append = false, $finish = false, $optional = false)
    {
        if ($loop === 0) $loop = $this->parameters;
        
        $noloop = false;
        $parsedloop = false;
        $loopopt = false;
        if ($loop > 2)
        {
            $noloop = $loop & TPL_NOLOOP;
            $include = $loop & TPL_INCLUDE;
            $append = $loop & TPL_APPEND;
            $finish = $loop & TPL_FINISH;
            $optional = $loop & TPL_OPTIONAL;
            $parsedloop = $loop & TPL_LOOP_INNER_PARSED;
            $loopopt = $loop & TPL_LOOP_INNER_OPTIONAL;
        }
        else
        {
            $noloop = $loop & TPL_NOLOOP;
        }
        
        if ($append and isset($this->vars[$target]))
        {
            $app = $this->get_var($target);
        }
        else
        {
            $app = '';
        }

        $this->set_var($target, $this->get_var($handle));

        if ($include) { $this->set_var($target, $this->include_files($target)); }

		if ($noloop) { $this->set_var($target, $this->parse_loops($target, TPL_NOLOOP | $loopopt | $parsedloop)); }
        elseif ($loop) { $this->set_var($target, $this->parse_loops($target, $loopopt | $parsedloop)); }

        if ($optional) { $this->set_var($target, $this->optional($this->get_var_silent($target))); }

        if ($append) { $this->set_var($target, $app . $this->parse($this->get_var_silent($target))); }
                else { $this->set_var($target, $this->parse($this->get_var($target))); }

        if ($finish) { $this->set_var($target, $this->finish($this->get_var($target))); }

        return $this->get_var($target);
    }

    function read_file($filename)
    {
        $filename = $this->root . $filename;

        if (!file_exists($filename))
        {
            $this->error("phemplate::read_file(): file $filename does not exist.", 'fatal');
            return '';
        }

        $tmp = false;
        
        $filesize = filesize($filename);
        if ($filesize)
        {
            $tmp = fread($fp = fopen($filename, 'r'), $filesize);
            fclose($fp);
        }
        return $tmp;
    }

    function drop_loop($loop_handle)
    {
        if (isset($this->loops[$loop_handle])) unset($this->loops[$loop_handle]);
    }

    function drop_var($handle)
    {
        if (isset($this->vars[$handle])) unset($this->vars[$handle]);
    }

    function set_error_handler(&$eh)
    {
        $this->error_handler =& $eh;
    }

    function set_block_syntax($start, $end)
    {
        if (!strpos($start, '|')) $this->error("phemplate::set_block_syntax(): no '|' in start tag", 'fatal');
        if (!strpos($end, '|')) $this->error("phemplate::set_block_syntax(): no '|' in end tag", 'fatal');
        $this->block_start_string = $start;
        $this->block_end_string = $end;
    }

    function error( $msg, $level = '')
    {
        if (isset($this->error_handler))
        {
            $lvl = E_USER_WARNING;
            if ('fatal' == $level) $lvl = E_USER_ERROR;
            $this->error_handler->report($lvl, $msg);
        }
        else
        {
            echo "\n<br><font color='red'><b>$level:</b> $msg</font><br>\n";
            if ('fatal' == $level) { exit; }
        }
    }

}

?>
