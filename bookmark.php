<?php
/*
Plugin Name: 2Focus Bookmarks 
Version: 0.1
Plugin URI: http://2focus.org/tools.php
Description: Плагин для добавления пользователями записей блога в сервисы закладок
Author: loly
Author URI: http://2focus.org/userb.php?uname=loly
*/

$version = '0.1';

$bookmark_service = array ('google' => array('title' => 'Google Bookmarks', 'uri' => 'http://www.google.com/bookmarks/mark?op=add&bkmk=<LINK>&title=<TITLE>'),
                    'digg' => array('title' => 'Digg', 'uri' => 'http://digg.com/submit?url=<LINK>'),
                    'reddit' => array ('title' => 'Reddit', 'uri' => 'http://reddit.com/submit?url=<LINK>&title=<TITLE>'),
                    'delicious' => array('title' => 'del.icio.us', 'uri' => 'http://del.icio.us/post?url=<LINK>&title=<TITLE>'),
                    'magnolia' => array('title' => 'Ma.gnolia', 'uri' => 'http://ma.gnolia.com/beta/bookmarklet/add?url=<LINK>&title=<TITLE>&description=<TITLE>'),
                    'technorati' => array ('title' => 'Technorati', 'uri' => 'http://www.technorati.com/faves?add=<LINK>'),
                    'news2ru' => array ('title' => 'News2.ru', 'uri' => 'http://news2.ru/add_story.php?url=<LINK>'),
                    'focus' => array ('title' => '2focus.org', 'uri' => 'http://2focus.org/add.php?popup=y&g_url=<LINK>&g_title=<TITLE>&g_desc='),
                    'bobrdobr' => array ('title' => 'БобрДобр.ru', 'uri' => 'http://www.bobrdobr.ru/addext.html?url=<LINK>&title=<TITLE>'),
                    'rumarkz' => array ('title' => 'RUmarkz', 'uri' => 'http://rumarkz.ru/bookmarks/?action=add&popup=1&address=<LINK>&title=<TITLE>'),
                    'vaau' => array ('title' => 'Ваау!', 'uri' => 'http://www.vaau.ru/submit/?action=step2&url=<LINK>'),
                    'memori' => array ('title' => 'Memori.ru', 'uri' => 'http://memori.ru/link/?sm=1&u_data[url]=<LINK>&u_data[name]=<TITLE>'),
                    'moemesto' => array ('title' => 'МоёМесто.ru', 'uri' => 'http://moemesto.ru/post.php?url=<LINK>&title=<TITLE>'),
                    'mrwong' => array ('title' => 'Mister Wong', 'uri' => 'http://www.mister-wong.ru/index.php?action=addurl&bm_url=<LINK>&bm_description=<TITLE>'),
                    'yandex' => array ('title' => 'Яндекс Закладки', 'uri' => 'http://zakladki.yandex.ru/userarea/links/addfromfav.asp?bAddLink_x=1&lurl=<LINK>&lname=<TITLE>')
                   );

$images_path = get_bloginfo('wpurl') . '/wp-content/plugins/2focus/images/';
$separator = ' ';

add_option('f_manual_insert', FALSE, '2focus Plugin');// использовать ручное добавление кода
add_option('f_use_nofollow', TRUE, '2focus Plugin');// использовать rel="nofollow"
add_option('f_target_blank', TRUE, '2focus Plugin');// использовать target="_blank"
add_option('f_use_noindex', FALSE, '2focus Plugin');// использовать <noindex>
add_option('f_use_bigicons', FALSE, '2focus Plugin');// использовать большие иконки
add_option('f_use_allpage', FALSE, '2focus Plugin');// отображать плагин на всех страницах
add_option('f_use_postpage', TRUE, '2focus Plugin');// отображать плагин на страницах постов
add_option('f_use_page', TRUE, '2focus Plugin');// отображать плагин на отдельных страницах
    
// какие ссылки отображать, по дефолту все включены
foreach ($bookmark_service as $key => $value) {
    $option_name = 'bookmarks_show_' . $key;
    add_option($option_name, TRUE, '2focus Plugin');
}

function focus_header() {
	$site_uri = get_settings('siteurl');
	$plugin_uri = $site_uri . '/wp-content/plugins/2focus/';
	echo '<link rel="stylesheet" type="text/css" href="' . $plugin_uri . 'bookmark.css?version=2.2" />';
}

add_action('wp_head', 'focus_header');

$manual_insert = get_option('f_manual_insert');
$use_nofollow = get_option('f_use_nofollow');
$target_blank = get_option('f_target_blank');
$use_noindex = get_option('f_use_noindex');
$use_bigicons = get_option('f_use_bigicons');
$use_allpage = get_option('f_use_allpage');
$use_postpage = get_option('f_use_postpage');
$use_page = get_option('f_use_page');

function getBookmarkLink ($service, $post_link, $post_title)
{

    global $bookmark_service, $images_path, $use_nofollow, $target_blank, $use_bigicons;
    if(!$use_bigicons){
        $link_uri = preg_replace("|<LINK>|", $post_link, $bookmark_service[$service]['uri']);
        $link_uri = preg_replace("|<TITLE>|", $post_title, $link_uri);
        $img = '<img src="' . $images_path . $service . '.png" border="0" width="16" height="16" alt="' . $bookmark_service[$service]['title'] . '" title="' . $bookmark_service[$service]['title'] . '">';
        $link = '<a href="' . $link_uri . '"';
        if ($use_nofollow) $link .= ' rel="nofollow"';
        if ($target_blank) $link .= ' target="_blank"';
        $link .= '>' . $img . '</a>';
        return $link;     
    }else{
        $link_uri = preg_replace("|<LINK>|", $post_link, $bookmark_service[$service]['uri']);
        $link_uri = preg_replace("|<TITLE>|", $post_title, $link_uri);
        $link = '<a href="' . $link_uri . '"';
        if ($use_nofollow) $link .= ' rel="nofollow"';
        if ($target_blank) $link .= ' target="_blank"';
        $link .= 'class="big" id="'.$service.'"';
        $link .= 'title="добавить в закладки на '.$bookmark_service[$service]['title'].'"';
        $link .='</a>';
        return $link; 
    }
    
}

function focus($text = '')
{
    global $post, $manual_insert, $bookmark_service, $separator, $use_allpage, $use_postpage, $use_page;
    if(($use_allpage)||(($use_postpage)&& (is_single()))||(($use_page)&&(is_page()))){
    
    $post_title = urlencode(stripslashes($post->post_title) . ' - ' . get_bloginfo('name') );
    $post_link = get_permalink($post->ID);
    $bookmark_list = "\n" . '<div class="bookmark">';
    $bookmark_list = get_option('f_use_noindex') ? $bookmark_list.'<noindex>' : $bookmark_list;
    foreach ($bookmark_service as $key => $value) {
        if (get_option('bookmarks_show_' . $key)) {
            $bookmark_list .= getBookmarkLink($key, $post_link, $post_title) . $separator;
        }
    }
    $bookmark_list = get_option('f_use_noindex') ? $bookmark_list.='</noindex></div>' : $bookmark_list.='</div>';
   
    if ($manual_insert) {
        echo $bookmark_list;
        return true;
    } else {
        return $text . $bookmark_list;
    }
    }
    else 
    return $text;
}

if (!$manual_insert) add_action('the_content', 'focus');

function getLatestVersion()
{
    $fp = fsockopen ("2focus.org", 80);

    $headers = "GET /tools/wpplugin.txt HTTP/1.1\r\n";
    $headers .= "Host: www.2ocus.org\r\n";
    $headers .= "Connection: Close\r\n\r\n";

    fwrite ($fp, $headers);
    $str = '';

    while (!feof ($fp))
    {
     $str .= fgets($fp, 1024);
    }

    fclose($fp);
    
    $latest_version = 'неизвестна';
    if (strpos($str, '2focus_bookmark_v:') != FALSE) { $latest_version = substr($str,strpos($str, '2focus_bookmark_v:') + 13); }
    return $latest_version;
}

function bookmarkzOptionsPage()
{
    global $version, $bookmark_service, $images_path;
    
	if (isset($_POST['update_options'])) {
		// обновляем настройки
		if (isset($_POST['manual_insert'])) {
            update_option('f_manual_insert', $manual_insert = TRUE);	
		} else {
		    update_option('f_manual_insert', $manual_insert = FALSE);
		}
        
		if (isset($_POST['use_nofollow'])) {
            update_option('f_use_nofollow', $use_nofollow = TRUE);	
		} else {
		    update_option('f_use_nofollow', $use_nofollow = FALSE);
		}

		if (isset($_POST['target_blank'])) {
            update_option('f_target_blank', $target_blank = TRUE);	
		} else {
		    update_option('f_target_blank', $target_blank = FALSE);
		}

    if (isset($_POST['use_noindex'])) {
                update_option('f_use_noindex', $use_noindex = TRUE);	
    		} else {
    		    update_option('f_use_noindex', $use_noindex = FALSE);
    		}
    if (isset($_POST['use_bigicons'])) {
                update_option('f_use_bigicons', $use_bigicons = TRUE);	
    		} else {
    		    update_option('f_use_bigicons', $use_bigicons = FALSE);
    		}
    if (isset($_POST['use_allpage'])) {
                update_option('f_use_allpage', $use_allpage = TRUE);	
    		} else {
    		    update_option('f_use_allpage', $use_allpage = FALSE);
    		}	    		
    if (isset($_POST['use_postpage'])) {
                update_option('f_use_postpage', $use_postpage = TRUE);	
    		} else {
    		    update_option('f_use_postpage', $use_postpage = FALSE);
    		}
    if (isset($_POST['use_page'])) {
                update_option('f_use_page', $use_page = TRUE);	
    		} else {
    		    update_option('f_use_page', $use_page = FALSE);
    		}	    		
        foreach ($bookmark_service as $key => $value) {
            if (isset($_POST['show_' . $key])) {
                $option_name = 'bookmarks_show_' . $key;
                update_option($option_name, $show[$key] = TRUE);
            } else {
                $option_name = 'bookmarks_show_' . $key;
                update_option($option_name, $show[$key] = FALSE);
            }
        }

	} else {
		// загружаем текущие настройки из базы
		$manual_insert = get_option('f_manual_insert');
		$use_nofollow = get_option('f_use_nofollow');
		$target_blank = get_option('f_target_blank');
		$use_noindex = get_option('f_use_noindex');
		$use_bigicons = get_option('f_use_bigicons');
    $use_allpage = get_option('f_use_allpage');
    $use_postpage = get_option('f_use_postpage');
    $use_page = get_option('f_use_page');
    
        foreach ($bookmark_service as $key => $value) {
            $option_name = 'bookmarks_show_' . $key;
            $show[$key] = get_option($option_name);
        }
	}

?>
<div class="wrap">
    <h2>Настройки 2Focus Bookmarks</h2>
    <?php $latest_version = getLatestVersion(); ?>
    <p><?php if ($version == $latest_version) { ?>
    У вас установлена свежая версия плагина: <strong><?php echo $version ?></strong>.
    <?php } else { ?>  
    Ваша верcия плагина: <?php echo $version ?>, текущая: <strong><?php echo $latest_version; ?></strong>.
    Возможно, есть смысл <a href="http://www.2focus.org/tools.php">обновить плагин</a>?<?php } ?></p>
    
    <form method="post">

        <fieldset class="options">
        <legend>Иконки:</legend>
        <p>
      <label><input name="use_bigicons" type="checkbox" <?php checked(TRUE, $use_bigicons); ?> class="tog"/>
		Использовать БОЛЬШИЕ иконки.
     </label><p><img src="<?php echo $images_path.'serv.png' ?>"></p>
		    </p>
    </fieldset>
    <table width="100%"><tr><td width="50%">
    <fieldset class="options">
        <legend>Сервисы Закладок:</legend>
        <p>
        
        <?php
        foreach ($bookmark_service as $key => $value) {
        ?>
        <label>
        <input name="show_<?php echo $key; ?>" type="checkbox" <?php checked(TRUE, $show[$key]); ?> class="tog"/>
        <img src="<?php echo $images_path . $key; ?>.png" width="16" height="16" border="0" align="absmiddle"> <?php echo $bookmark_service[$key]['title']; ?>
        </label><br /></p><p>
        
        <?php
        }
        ?>
		</p>
    </fieldset></td><td width="50%" valign="top">
    <fieldset class="options">
    <legend>Управление отображение:</legend>
        <p>

        <p><label>
        <input name="use_allpage" type="checkbox" <?php checked(TRUE, $use_allpage); ?> class="tog"/>
		    <b>Использовать на всех страницах.</b><br /><br />
        Указанный аттрибут разрешает отображать ссылки на Закладки на всех доступных страницах (главная, записи, отдельные страницы)
        </label></p>
        
        <p><label>
        <input name="use_postpage" type="checkbox" <?php checked(TRUE, $use_postpage); ?> class="tog"/>
		    <b>Использовать только на страницах записей.</b><br /><br />
        При использовании этого аттрибута ссылки на Закладки отображаются только на странице записи, не превращая вашу "главную" в новогоднюю гирлянду :)
        </label></p>
        
        <p><label>
        <input name="use_page" type="checkbox" <?php checked(TRUE, $use_page); ?> class="tog"/>
		    <b>Использовать на отдельных страницах.</b><br /><br />
        Разрешает отображать плагин на отдельных страницах
        </label></p>

		</p>
    </td></tr></table>
   
    <p class="submit"><input type="submit" class="submit" name="update_options" value="Сохранить настройки &raquo;" /></p>
    
	<fieldset class="options">
		<legend>Настройки кода:</legend>

        <p><label>
        <input name="manual_insert" type="checkbox" <?php checked(TRUE, $manual_insert); ?> class="tog"/>
		Использовать ручную вставку кода.<br /> <br />
        Плагин автоматически добавляет иконки со ссылками на популярные сети социальных закладок
        в конец каждого сообщения в блоге. Если вы хотите вручную управлять отображением иконок, вставьте в свой шаблон
        следующий кусок кода: <em>&lt;?php focus(); ?&gt;</em> там, где вы хотите вывести иконки. Обратите внимание,
        что этот код должен быть размещен внутри цикла <em>TheLoop</em>, т.е. между
        <em>&lt;?php while (have_posts()) : the_post(); ?&gt;</em> и <em>&lt;?php endwhile; ?&gt;</em><br /> <br />
        Управлять внешним видом иконок можно через файл стилей <em>bookmark.css</em> в каталоге с плагином <br />
        </label></p>

        <p><label>
        <input name="use_nofollow" type="checkbox" <?php checked(TRUE, $use_nofollow); ?> class="tog"/>
		Использовать аттрибут <em>rel="nofollow"</em>.<br /> <br />
        Указанный аттрибут запрещает поисковой системе <b>Google</b> переход по ссылке. Если вы не уверены, нужно это вам или нет,
        просто оставьте как есть.
        </label></p>
        
          <p><label>
        <input name="use_noindex" type="checkbox" <?php checked(TRUE, $use_noindex); ?> class="tog"/>
		  Запрещает к индексации Яндексом ссылок на Сервисы Закладок (использовать тег <em>noindex</em>).<br /> <br />
		  Указанный аттрибут запрещает поисковой системе <b>Яндекс</b> переход по ссылке. Если вы не уверены, нужно это вам или нет,
        просто оставьте как есть.
        </label></p>
        
        <p><label>
        <input name="target_blank" type="checkbox" <?php checked(TRUE, $target_blank); ?> class="tog"/>
		Открывать ссылки в новом окне.
        </label></p>
        
	</fieldset>
		
    <p class="submit"><input type="submit" class="submit" name="update_options" value="Сохранить настройки &raquo;" /></p>
    </form>	
</div>
<?php
}

function focusAddMenu() {
		add_options_page('focus', 'Закладки', 8, __FILE__, 'bookmarkzOptionsPage');
}

add_action('admin_menu', 'focusAddMenu');

?>