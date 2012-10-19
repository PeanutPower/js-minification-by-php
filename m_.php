<?
$minify_level = isset($minify_level) ? $minify_level : 2;
/*
$minify_level = 0 - no minify, no obfuscator
$minify_level = 1 - minify, no obfuscator
$minify_level = 2 - minify, obfuscator
$minify_level = 3 - no minify, obfuscator
*/

$intminify = isset($intminify) ? $intminify : true;
/*
$intminify = true - ise internal function;
$intminify = false - ise external class (http://github.com/rgrove/jsmin-php/)
*/

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
{
   	preg_match("~\.(.*)$~U",$_SERVER['PATH_INFO'],$ext);
	if(strtolower(trim($ext[0])) == '.js')
	{
		header("Content-Type: application/x-javascript");
	}
	elseif(strtolower(trim($ext[0])) == '.css')
	{
		header("Content-Type: text/css");
	}
	//header("Content-Type: text/css");
   	header("Expires: ".gmdate("D, d M Y H:i:s", time()+86400*365)." GMT");
	header("Cache-Control: max-age="+86400*365);
   	$cashdir = "m_";
	$pathinfo = $_SERVER['PATH_INFO'];
   	preg_match("~\/(.*)\.~U",$pathinfo,$mdf);
   	header("Etag: ".$mdf[1]);
	if($_SERVER['HTTP_IF_NONE_MATCH'] and $_SERVER['HTTP_IF_NONE_MATCH'] == $mdf[1])
	{
		header ("HTTP/1.0 304 Not Modified");
		header ('Content-Length: 0');
		exit;
	}
	
	if( eregi("debug", $pathinfo) )
	{
		debug();
	}
	else
	{
		echo @file_get_contents($cashdir."/".$mdf[1]);
	}
}

function minify_get_link($urls)
{
	global $minify_level, $intminify;
	$mainuri 	 = "http://www.yonad.".eregi_replace(".*\.(.*)$","\\1",$_SERVER['HTTP_HOST']);
	$root 		 = $_SERVER['DOCUMENT_ROOT'];
	$mdf  		 = "";
	$cashdir 	 = realpath($_SERVER['DOCUMENT_ROOT'])."/m_";
	$content_js  = "";
	$content_css = "";
	
	if(count($urls))
	{
		foreach($urls as $url)
		{
			$url = trim($url);
			
			if($url)
			{
				$file = $root.$url;
				if( file_exists($file) ) 
				{	
					preg_match("~\.(.*)$~U", $file, $ext);
					
					if($ext[0] == '.js')
					{
						$files_js[] = $file;
					}
					elseif($ext[0] == '.css')
					{
						$files_css[] = $file;
					}
				}
			}
		}
	}
	
	if(count($files_js))
	{
		foreach($files_js as $file)
		{
			if($minify_level == 3)
			{
				$content_js.= obfuscator( file_get_contents($file) );
			}
			elseif($minify_level == 2)
			{
				$content_js.= $intminify ? obfuscator(mini(file_get_contents($file))) : 
										obfuscator(JSMin::minify(file_get_contents($file)));
			}
			elseif($minify_level == 1)
			{
				$content_js.= $intminify ? mini(file_get_contents($file)) : JSMin::minify(file_get_contents($file));
			}
			else
			{
				$content_js.= file_get_contents($file);
			}
		}
	}
	
	if($content_js)
	{
		$mdf  = md5($content_js);
		
		if( !file_exists($cashdir) ) mkdir( $cashdir , 0777 );
		if( file_exists($cashdir) )
		{
			file_put_contents($cashdir."/".$mdf, $content_js);
		}
		
		if($mdf) echo "<script  type=\"text/javascript\">document.write(\"<scr\"+\"ipt  language='javascript' src='$mainuri/m_.php/$mdf.js'></\"+\"script>\")</script>";
	}
	
	if(count($files_css))
	{
	foreach($files_css as $file)
	{
		if($minify_level == 3)
		{
			$content_css.= $intminify ? mini_css(file_get_contents($file)) : JSMin::minify(file_get_contents($file));
		}
		elseif($minify_level == 2)
		{
			$content_css.= $intminify ? mini_css(file_get_contents($file)) : JSMin::minify(file_get_contents($file));
		}
		elseif($minify_level == 1)
		{
			$content_css.= $intminify ? mini_css(file_get_contents($file)) : JSMin::minify(file_get_contents($file));
		}
		else
		{
			$content_css.= file_get_contents($file);
		}
	}
	}
	
	if($content_css)
	{
		$mdf  = md5($content_css);
		
		if( !file_exists($cashdir) ) mkdir( $cashdir , 0777 );
		if( file_exists($cashdir) )
		{
			file_put_contents($cashdir."/".$mdf, $content_css);
		}
		
		if($mdf) echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$mainuri/m_.php/$mdf.css\" />";
	}
}

function debug()
{
	global $minify_level, $intminify;
	//$urls[] = "/js/map.js";
	$urls[] = "/js/all.js";
	//$urls[] = "/ynd_calendar/kalendar.js";
	//$urls[] = "/styles/style.css";
	$root = $_SERVER['DOCUMENT_ROOT'];
	$cashdir = "m_";
	
	if(count($urls))
	{
		foreach($urls as $url)
		{
			$url = trim($url);
			
			if($url)
			{
				$file = $root.$url;
				if( file_exists($file) ) 
				{	
					$files[] = $file;
				}
			}
		}
	}
	
	foreach($files as $file)
	{
		if($minify_level == 3)
		{
			$content.= obfuscator( file_get_contents($file) );
		}
		elseif($minify_level == 2)
		{
			$content.= $intminify ? obfuscator(mini(file_get_contents($file))) : 
									obfuscator(JSMin::minify(file_get_contents($file)));
		}
		elseif($minify_level == 1)
		{
			$content.= $intminify ? mini(file_get_contents($file)) : JSMin::minify(file_get_contents($file));
		}
		else
		{
			$content.= file_get_contents($file);
		}
	}
	
	echo $content;
}

function mini_css($text)
{
	$output = preg_replace('#/\*.*?\*/#s', '', $text);
    $output = preg_replace('/\s*([{}|:;,])\s+/', '$1', $output);
	//$output = preg_replace('/(\W)\s+/', '$1', $output);
    $output = str_replace(';}', '}', $output);
    return $output;
}

function mini($text)
{
	$text = preg_replace("~\/\*.*\*\/~U","",$text);
	$text = preg_replace("~\/\/.*~","",$text);
	$text = preg_replace("~\r\n~","\n",$text);
	$text = preg_replace("~\t~","",$text);
	$text = preg_replace("~(\s)*~","\\1",$text);
	$text = preg_replace("~;\s*~",";",$text);
	$text = preg_replace("~([\(\{])\n+~","\\1",$text);
	$text = preg_replace("~;+([\)\}])~","\\1",$text);
	$text = preg_replace("~\)\s\{~","){",$text);
	$text = preg_replace("~\}\s*\}~","}}",$text);
	$text = preg_replace("~\s*([\}\{])~","\\1",$text);
	$text = preg_replace("~\}\n(var )~","};\\1",$text);
	$text = preg_replace("~([^\'\"]) ?([=\-\+\:;\.\?\<\>\)\(\,\}\{\&\*\^\%\|\/!]) ?([^\'\"])~","\\1\\2\\3",$text);
	$text = preg_replace("~\s+\{~","{",$text);
	$text = preg_replace("~;+~",";",$text);
	return $text;
}

function obfuscator($text)
{
	// add markers for correct plucking functions code;
	$text = preg_replace("~(function\s?[^\(])~","¯ª¬\\1",$text);
	$parts = explode("¯ª¬", $text);
	$afun = "";
	foreach($parts as $test)
	{
		if(!eregi("^function",$test) )
		{
			$afun.= $test;
		}
		else
		{
			$marked_text = preg_replace("~\}~", "}¬ª¯", $test);
			
			$get_bracket = explode("¬ª¯", $marked_text);
			$counter = $setmark = 0;
			foreach($get_bracket as $basket)
			{
				$afun.= $basket;
				$counter+= preg_match_all("~\{~", $basket, $_);
				$counter = $counter - 1;
				if(!$counter and !$setmark)
				{ 
					$afun.= "¯ª¬";
					$setmark = true;
				}
			}
		}
	}
	$text = preg_replace("~(function\s?[^\(])~","¯ª¬\\1",$afun);
	
	// explode code to functions fragments by markers
	$functions  = explode("¯ª¬", $text);
	$nf = "";
	
	for($i=0; $i < count($functions); $i++)
	{
		$f = $functions[$i];
	//echo ">>> $f\n";
		if( $i and eregi("^function",$f) )
		{
			//prepare short vars array
			$short_vars = str_split("abcdefghijklmnopqrstuvwxyz");
			foreach($short_vars as $l)
			{
				for($ii=1; $ii<10; $ii++)
				{
					$add_vars[] = $l.$ii;
				}
			}
			$short_vars = array_merge($short_vars, $add_vars);
			
			// detect external varnames in ()
			$rvars = array();
			$nof = preg_replace("~\{\}~","",$f);
			preg_match("~\([^{(]*\)~U", $nof, $match);
			$ivars = explode(',',trim($match[0],"()"));
			foreach($ivars as $iv)
			{
				$iv = trim($iv);
				if($iv and !eregi("['\"]",$iv))
				{
					$nvar = array_shift($short_vars);
					if($nvar) $rvars[$iv] = $nvar;
					else $rvars[$iv] = $iv;
				}
			}
			
			// detect internal varnames in ()
			$noq = preg_replace("~(\\\")|(\\\')~","",$f);
			$noq = preg_replace("~\"[^\"]*\"~U","",$noq);
			$noq = preg_replace("~\'[^\']*\'~U","",$noq);
			preg_match_all("@[^\w\.][^\W\.]+\s?=[^=]@U", $noq, $match2);
			foreach($match2[0] as $iv)
			{
				$iv = preg_replace("@\W@","",$iv);
				if($iv and !eregi("\.",$iv) and !$rvars[$iv])
				{
					$nvar = array_shift($short_vars);
					if($nvar) $rvars[$iv] = $nvar;
					else $rvars[$iv] = $iv;
				}
			}
			
			// correct collision 
			foreach($rvars as $key=>$val)
			{
				if( $key == $val) unset($rvars[$key]); // delete same exchages
				if( $rvars[$val] and $key!=$val) $rvars[$key] = $val."_";
			}
			
			//strip code in quotes
			$f = preg_replace("~\\\'~","OnEqUoTe",$f);
			$f = preg_replace('~\\\"~',"DbLqUoTe",$f);		
			preg_match_all('~([\'\"]).*\1~U', $f, $quotes);
	//print_r($quotes);
			$nextquote = 0;
			foreach($quotes[0] as $quote)
			{
	//echo ":>:: ".$quote."\n";
				$quote = addcslashes($quote,'\^$.[]|()?*{}-');
	//echo ":<:: ".$quote."\n";
				$nextquote++;	
				$f = preg_replace("@$quote@","-_-".$nextquote."-_-",$f);
	//echo "<.< ".$f."\n";
			}
			
			if(count($rvars))
			{
	//print_r($rvars);
				foreach($rvars as $from => $to)
				{
					//echo "from: ".$from."\n";
					if( strlen($from) > 2 ) $f = preg_replace("@\b$from\b@","$to",$f);
				}
			}
	//print_r($quotes);
			// additional safe strip spaces
			$f = preg_replace("~\s(\W)~","\\1",$f);
			$f = preg_replace("~(\W) ~U","\\1",$f);
			$nextquote = 0;
			foreach($quotes[0] as $quote)
			{
				$quote = addcslashes($quote,'\\');
				$nextquote++;
				$f = preg_replace("@-_-$nextquote-_-@","$quote",$f);
			}
			$f = preg_replace('~DbLqUoTe~','\"',$f);
			$f = preg_replace("~OnEqUoTe~","\'",$f);
	//echo "<<<";		
	//echo "<<< ".$f."\n";		
	
		}
		$nf.= $f;
	}
	return $nf;
}

/**
 * jsmin.php - PHP implementation of Douglas Crockford's JSMin.
 *
 * This is pretty much a direct port of jsmin.c to PHP with just a few
 * PHP-specific performance tweaks. Also, whereas jsmin.c reads from stdin and
 * outputs to stdout, this library accepts a string as input and returns another
 * string as output.
 *
 * PHP 5 or higher is required.
 *
 * Permission is hereby granted to use this version of the library under the
 * same terms as jsmin.c, which has the following license:
 *
 * --
 * Copyright (c) 2002 Douglas Crockford  (www.crockford.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * The Software shall be used for Good, not Evil.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * --
 *
 * @package JSMin
 * @author Ryan Grove <ryan@wonko.com>
 * @copyright 2002 Douglas Crockford <douglas@crockford.com> (jsmin.c)
 * @copyright 2008 Ryan Grove <ryan@wonko.com> (PHP port)
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @version 1.1.1 (2008-03-02)
 * @link http://code.google.com/p/jsmin-php/
 */

class JSMin {
  const ORD_LF    = 10;
  const ORD_SPACE = 32;

  protected $a           = '';
  protected $b           = '';
  protected $input       = '';
  protected $inputIndex  = 0;
  protected $inputLength = 0;
  protected $lookAhead   = null;
  protected $output      = '';

  // -- Public Static Methods --------------------------------------------------
  

  public static function minify($js) {
    $jsmin = new JSMin($js);
    return $jsmin->min();
  }

  // -- Public Instance Methods ------------------------------------------------

  public function __construct($input) {
    $this->input       = str_replace("\r\n", "\n", $input);
    $this->inputLength = strlen($this->input);
  }

  // -- Protected Instance Methods ---------------------------------------------

  protected function action($d) {
    switch($d) {
      case 1:
        $this->output .= $this->a;

      case 2:
        $this->a = $this->b;

        if ($this->a === "'" || $this->a === '"') {
          for (;;) {
            $this->output .= $this->a;
            $this->a       = $this->get();

            if ($this->a === $this->b) {
              break;
            }

            if (ord($this->a) <= self::ORD_LF) {
              throw new JSMinException('Unterminated string literal.');
            }

            if ($this->a === '\\') {
              $this->output .= $this->a;
              $this->a       = $this->get();
            }
          }
        }

      case 3:
        $this->b = $this->next();

        if ($this->b === '/' && (
            $this->a === '(' || $this->a === ',' || $this->a === '=' ||
            $this->a === ':' || $this->a === '[' || $this->a === '!' ||
            $this->a === '&' || $this->a === '|' || $this->a === '?')) {

          $this->output .= $this->a . $this->b;

          for (;;) {
            $this->a = $this->get();

            if ($this->a === '/') {
              break;
            } elseif ($this->a === '\\') {
              $this->output .= $this->a;
              $this->a       = $this->get();
            } elseif (ord($this->a) <= self::ORD_LF) {
              throw new JSMinException('Unterminated regular expression '.
                  'literal.');
            }

            $this->output .= $this->a;
          }

          $this->b = $this->next();
        }
    }
  }

  protected function get() {
    $c = $this->lookAhead;
    $this->lookAhead = null;

    if ($c === null) {
      if ($this->inputIndex < $this->inputLength) {
        $c = substr($this->input, $this->inputIndex, 1);
        $this->inputIndex += 1;
      } else {
        $c = null;
      }
    }

    if ($c === "\r") {
      return "\n";
    }

    if ($c === null || $c === "\n" || ord($c) >= self::ORD_SPACE) {
      return $c;
    }

    return ' ';
  }

  protected function isAlphaNum($c) {
    return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
  }

  protected function min() {
    $this->a = "\n";
    $this->action(3);

    while ($this->a !== null) {
      switch ($this->a) {
        case ' ':
          if ($this->isAlphaNum($this->b)) {
            $this->action(1);
          } else {
            $this->action(2);
          }
          break;

        case "\n":
          switch ($this->b) {
            case '{':
            case '[':
            case '(':
            case '+':
            case '-':
              $this->action(1);
              break;

            case ' ':
              $this->action(3);
              break;

            default:
              if ($this->isAlphaNum($this->b)) {
                $this->action(1);
              }
              else {
                $this->action(2);
              }
          }
          break;

        default:
          switch ($this->b) {
            case ' ':
              if ($this->isAlphaNum($this->a)) {
                $this->action(1);
                break;
              }

              $this->action(3);
              break;

            case "\n":
              switch ($this->a) {
                case '}':
                case ']':
                case ')':
                case '+':
                case '-':
                case '"':
                case "'":
                  $this->action(1);
                  break;

                default:
                  if ($this->isAlphaNum($this->a)) {
                    $this->action(1);
                  }
                  else {
                    $this->action(3);
                  }
              }
              break;

            default:
              $this->action(1);
              break;
          }
      }
    }

    return $this->output;
  }

  protected function next() {
    $c = $this->get();

    if ($c === '/') {
      switch($this->peek()) {
        case '/':
          for (;;) {
            $c = $this->get();

            if (ord($c) <= self::ORD_LF) {
              return $c;
            }
          }

        case '*':
          $this->get();

          for (;;) {
            switch($this->get()) {
              case '*':
                if ($this->peek() === '/') {
                  $this->get();
                  return ' ';
                }
                break;

              case null:
                throw new JSMinException('Unterminated comment.');
            }
          }

        default:
          return $c;
      }
    }

    return $c;
  }

  protected function peek() {
    $this->lookAhead = $this->get();
    return $this->lookAhead;
  }
}

// -- Exceptions ---------------------------------------------------------------
class JSMinException extends Exception {}

?>
