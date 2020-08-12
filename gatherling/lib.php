<?php

require_once 'bootstrap.php';
if (isset($CONFIG['cookie_lifetime'])){
    ini_set('session.gc_maxlifetime', $CONFIG['cookie_lifetime']);
    ini_set('session.cookie_lifetime', $CONFIG['cookie_lifetime']);
    session_set_cookie_params($CONFIG['cookie_lifetime']);
}
header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
header('Referrer-Policy: strict-origin-when-cross-origin');

$HC = '#DDDDDD';
$R1 = '#EEEEEE';
$R2 = '#FFFFFF';
$CC = $R1;
date_default_timezone_set('US/Eastern'); // force time functions to use US/Eastern time

function is_assoc($array)
{
    return (bool) count(array_filter(array_keys($array), 'is_string'));
}

/** Gets the correct name, relative to the gatherling root dir, for a file in the theme.
 *  Allows for overrides, falls back to default/.
 */
function theme_file($name)
{
    global $CONFIG;
    if (Player::isLoggedIn()) {
        $user_dir = 'styles/'.Player::getSessionPlayer()->theme.'/';
        if (file_exists($user_dir.$name)) {
            return $user_dir.$name;
        }
    }
    $theme_dir = "styles/{$CONFIG['style']}/";
    $default_dir = 'styles/Chandra/';
    if (file_exists($theme_dir.$name)) {
        return $theme_dir.$name;
    }

    return $default_dir.$name;
}

function print_header($title, $js = null, $extra_head_content = '')
{
    global $CONFIG;

    // if player ip address changes could be a hacker breaking in.
    // Once you implement a remember me cookie, this will also
    // destroy the remember me cookie if the IP's don't match
    // if (Player::isLoggedIn()) {
    //     $player = new Player(Player::loginName());
    //     if ($player->getIPAddresss() != Player::getClientIPAddress()) {
    //         Player::logOut();
    //         redirect("login.php?ipaddresschanged=true");
    //     }
    // }

    echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
    echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\" xml:lang=\"en\">\n";
    echo "  <head>\n";
    echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
    echo "    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\" />\n";
    echo "    <meta name=\"google-site-verification\" content=\"VWE-XX0CgPTHYgUqJ6t13N75y-p_Q9dgtqXikM3EsBo\" />\n";
    echo "    <title>{$CONFIG['site_name']} | {$title}</title>\n";
    echo '    <link rel="stylesheet" type="text/css" media="all" href="'.theme_file('css/stylesheet.css')."\" />\n";
    echo "     <script src=\"//code.jquery.com/jquery-latest.min.js\"></script>\n";
    echo "     <script src=\"//cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js\"></script>\n";
    echo "     <script src=\"//cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.21/moment-timezone-with-data.min.js\"></script>\n";

    if ($js) {
        echo '    <script type="text/javascript">';
        echo $js;
        echo '    </script>';
    }
    echo $extra_head_content;
    echo <<<'EOT'
  </head>
  <body>
    <div id="maincontainer" class="container_12">
        <div id="header_bar" class="box">
            <div id="header_gatherling">
EOT;
    include_once 'analyticstracking.php'; // google analytics tracking
    //  echo image_tag("header_gatherling.png");
    echo <<<'EOT'
            </div>
            <div id="header_logo">
EOT;
    echo image_tag('header_logo.png');
    echo <<<EOT
            </div>
            <div id="action"></div>
        </div>
        <div id="mainmenu_submenu" class="grid_12 menubar">
        <ul>
          <li><span class=\"inputbutton\"><a href="./index.php">Home</a></span></li>
          <li><a href="https://discord.gg/F9SrMwV">Discord</a></li>
          <li><a href="./series.php">Events</a></li>
          <li><a href="./gatherling.php">Gatherling</a></li>
          <li><a href="./ratings.php">Ratings</a></li>
          <li><a href="https://pennydreadfulmagic.com/bugs/">Known Bugs</a></li>
        </ul>
      </div>
EOT;

    $player = Player::getSessionPlayer();

    $super = false;
    $host = false;
    $organizer = false;

    if ($player != null) {
        $host = $player->isHost();
        $super = $player->isSuper();
        $organizer = count($player->organizersSeries()) > 0;
    }

    $tabs = 5;
    if ($super || $organizer) {
        $tabs += 1;
    }
    if ($host) {
        $tabs += 1;
    }
    if ($super) {
        $tabs += 1;
    }

    echo <<<EOT
<div id="submenu" class="grid_12 tabs_$tabs menubar">
  <ul>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="player.php">Player CP</a></li>
    <li><a href="eventreport.php">Metagame</a></li>
    <li><a href="decksearch.php">Deck Search</a></li>
EOT;
    if ($player == null) {
        echo "<li class=\"last\"><a href=\"login.php\">Login</a></li>\n";
    } else {
        echo "<li class=\"last\"><a href=\"logout.php\">Logout [{$player->name}]</a></li>\n";
    }

    if ($host || $super) {
        echo "<li><a href=\"event.php\">Host CP</a></li>\n";
    }

    if ($organizer || $super) {
        echo "<li><a href=\"seriescp.php\">Series CP</a></li>\n";
    }

    if ($organizer || $super) {
        echo "<li><a href=\"formatcp.php\">Format CP</a></li>\n";
    }

    if ($super) {
        echo "<li><a href=\"admincp.php\">Admin CP</a></li>\n";
    }

    echo "</ul> </div>\n";
}

function print_footer()
{
    echo "<div class=\"prefix_1 suffix_1\">\n";
    echo "<div id=\"gatherling_footer\" class=\"box\">\n";
    version_tagline();
    echo "</div><!-- prefix_1 suffix_1 -->\n";
    echo "</div><!-- gatherling_footer -->\n";
    echo "<div class=\"clear\"></div>\n";
    echo "</div> <!-- container -->\n";
    echo "<script src=\"action.js\" defer></script>\n";
    echo "</body>\n";
    echo "</html>\n";
}

function headerColor()
{
    global $HC, $CC, $R1, $R2;
    $CC = $R2;

    return $HC;
}

function rowColor()
{
    global $CC, $R1, $R2;
    if (strcmp($CC, $R1) == 0) {
        $CC = $R2;
    } else {
        $CC = $R1;
    }

    return $CC;
}

function linkToLogin($pagename = null, $message = null, $username = null)
{
    if (is_null($pagename)) {
        $pagename = $_SERVER['REQUEST_URI'];
    }
    if (is_null($message)) {
        $message = "You must log in to access $pagename.";
    }
    redirect("login.php?target=$pagename&message=$message&username=$username");

    echo "<center>\n";
    echo "<div class=\"error\">$message</div>";
    echo "Please <a href=\"login.php?target={$_SERVER['REQUEST_URI']}\">Click Here</a> to log in.\n";
    echo "</center>\n";
}

function printCardLink($card)
{
    $gathererName = preg_replace('/ /', ']+[', $card);
    $gathererName = str_replace('/', ']+[', $gathererName);
    echo '<span class="cardHoverImageWrapper">';
    echo "<a href=\"http://gatherer.wizards.com/Pages/Search/Default.aspx?name=+[{$gathererName}]\" ";
    echo "class=\"linkedCardName\" target=\"_blank\">{$card}<span class=\"linkCardHoverImage\"><p class=\"crop\" style=\"background-image: url(http://gatherer.wizards.com/Handlers/Image.ashx?name={$card}&type=card\"><img src=\"http://gatherer.wizards.com/Handlers/Image.ashx?name={$card}&type=card\"></p></span></a></span>";
}

function image_tag($filename, $extra_attr = null)
{
    $tag = '<img ';
    if (is_array($extra_attr)) {
        foreach ($extra_attr as $key => $value) {
            $tag .= "{$key}=\"{$value}\" ";
        }
    }
    $tag .= 'src="'.theme_file("images/{$filename}").'" />';

    return $tag;
}

function noHost()
{
    echo "<center>\n";
    echo "Only hosts and admins may access that page.</center>\n";
}

function medalImgStr($medal)
{
    return image_tag("$medal.png", ['style' => 'border-width: 0px']);
}

function seasonDropMenu($season, $useall = 0)
{
    $db = Database::getConnection();
    $query = 'SELECT MAX(season) AS m FROM events';
    $result = $db->query($query) or exit($db->error);
    $maxarr = $result->fetch_assoc();
    $max = $maxarr['m'];
    $title = ($useall == 0) ? '- Season - ' : 'All';
    $result->close();
    numDropMenu('season', $title, max(10, $max + 1), $season);
}

function formatDropMenu($format, $useAll = 0, $form_name = 'format', $show_meta = true)
{
    $db = Database::getConnection();
    $query = 'SELECT name FROM formats';
    if (!$show_meta) {
        $query .= ' WHERE NOT is_meta_format ';
    }
    $query .= ' ORDER BY priority desc, name';
    $result = $db->query($query) or exit($db->error);
    echo "<select class=\"inputbox\" name=\"{$form_name}\">";
    $title = ($useAll == 0) ? '- Format -' : 'All';
    echo "<option value=\"\">$title</option>";
    while ($thisFormat = $result->fetch_assoc()) {
        $name = $thisFormat['name'];
        $selStr = (strcmp($name, $format) == 0) ? 'selected' : '';
        echo "<option value=\"$name\" $selStr>$name</option>";
    }
    echo '</select>';
    $result->close();
}

function emailStatusDropDown($currentStatus = 1)
{
    echo '<select class="inputbox" name="emailstatus">';
    if ($currentStatus == 0) {
        echo '<option value="0" selected>Private</option>';
    } else {
        echo '<option value="0">Private</option>';
    }
    if ($currentStatus == 1) {
        echo '<option value="1" selected>Public</option>';
    } else {
        echo '<option value="1">Public</option>';
    }
    echo '</select>';
}

function dropMenu($name, $options, $selected = null)
{
    echo "<select class=\"inputbox\" name=\"{$name}\">";
    foreach ($options as $option) {
        $setxt = '';
        if (!is_null($selected) && $selected == $option) {
            $setxt = ' selected';
        }
        echo "<option value=\"{$option}\"{$setxt}>{$option}</option>";
    }
    echo '</select>';
}

function numDropMenu($field, $title, $max, $def, $min = 0, $special = '')
{
    if (strcmp($def, '') == 0) {
        $def = -1;
    }
    echo "<select class=\"inputbox\" name=\"$field\">";
    echo "<option value=\"\">$title</option>";
    if (strcmp($special, '') != 0) {
        $sel = ($def == 128) ? 'selected' : '';
        echo "<option value=\"128\" $sel>$special</option>";
    }
    for ($n = $min; $n <= $max; $n++) {
        $selStr = ($n == $def) ? 'selected' : '';
        echo "<option value=\"$n\" $selStr>$n</option>";
    }
    echo '</select>';
}

function timeDropMenu($hour, $minutes = 0)
{
    if (strcmp($hour, '') == 0) {
        $hour = -1;
    }
    echo '<select class="inputbox" name="hour">';
    echo '<option value="">- Hour -</option>';
    for ($h = 0; $h < 24; $h++) {
        for ($m = 0; $m < 60; $m += 30) {
            $hstring = $h;
            if ($m == 0) {
                $mstring = ':00';
            } else {
                $mstring = ":$m";
            }
            if ($h == 0) {
                $hstring = '12';
            }
            $apstring = ' AM';
            if ($h >= 12) {
                $hstring = $h != 12 ? $h - 12 : $h;
                $apstring = ' PM';
            }
            if ($h == 0 && $m == 0) {
                $hstring = 'Midnight';
                $mstring = '';
                $apstring = '';
            } elseif ($h == 12 && $m == 0) {
                $hstring = 'Noon';
                $mstring = '';
                $apstring = '';
            }
            $selStr = ($hour == $h) && ($minutes == $m) ? 'selected' : '';
            echo "<option value=\"$h:$m\" $selStr>$hstring$mstring$apstring</option>";
        }
    }
    echo '</select>';
}

function minutes($mins)
{
    return $mins * 60;
}

function json_headers()
{
    header('Content-type: application/json');
    header('Cache-Control: no-cache');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Access-Control-Allow-Origin: *');
}

function distance_of_time_in_words($from_time, $to_time = 0, $truncate = false)
{
    $inputSeconds = abs(($from_time - $to_time));

    $secondsInAMinute = 60;
    $secondsInAnHour = 60 * $secondsInAMinute;
    $secondsInADay = 24 * $secondsInAnHour;

    // extract days
    $days = floor($inputSeconds / $secondsInADay);

    // extract hours
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);

    // extract minutes
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);

    // extract the remaining seconds
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);

    $parts = [];
    if ($days > 7) {
        $weeks = floor($days / 7);
        $days = $days % 7;
        $parts[] = "$weeks Week".(($weeks > 1) ? 's' : '');
    }
    if ($days > 0) {
        $parts[] = "$days Day".(($days > 1) ? 's' : '');
    }
    if ($hours > 0) {
        $parts[] = "$hours Hour".(($hours > 1) ? 's' : '');
    }
    if ($minutes > 0) {
        $parts[] = "$minutes Minute".(($minutes > 1) ? 's' : '');
    }
    if ($truncate) {
        $parts = array_slice($parts, 0, 2);
    }

    return implode(', ', $parts);
}

function not_allowed($reason)
{
    echo "<span class=\"notallowed inputbutton\" title=\"{$reason}\">&#x26A0;</span>";
}

function displayPlayerEmailPopUp($player, $email)
{
    echo "<a class=\"emailPop\" style=\color: green\" title=\"{$email}\">{$player}</a>";
}

function tribeBanDropMenu($format)
{
    $allTribes = Format::getTribesList();
    $bannedTribes = $format->getTribesBanned();
    $tribes = array_diff($allTribes, $bannedTribes); // remove tribes banned from drop menu

    echo '<select class="inputbox" name="tribeban">';
    echo '<option value="Unclassified">- Tribe to Ban - </option>';
    foreach ($tribes as $tribe) {
        echo "<option value=\"$tribe\">$tribe</option>";
    }
    echo '</select>';
}

function subTypeBanDropMenu($format)
{
    $allSubTypes = Format::getTribesList();
    $bannedSubTypes = $format->getSubTypesBanned();
    $subTypes = array_diff($allSubTypes, $bannedSubTypes); // remove sub types banned from drop menu

    echo '<select class="inputbox" name="subtypeban">';
    echo '<option value="Unclassified">- Subtype to Ban - </option>';
    foreach ($subTypes as $subType) {
        echo "<option value=\"$subType\">$subType</option>";
    }
    echo '</select>';
}

function formatsDropMenu($formatType = '', $seriesName = 'System')
{
    $formatNames = [];

    if ($formatType == 'System') {
        $formatNames = Format::getSystemFormats();
    }
    if ($formatType == 'Public') {
        $formatNames = Format::getPublicFormats();
    }
    if ($formatType == 'Private') {
        $formatNames = Format::getPrivateFormats($seriesName);
    }
    if ($formatType == 'Private+') {
        $formatNames = array_merge(
            Format::getSystemFormats(),
            Format::getPublicFormats(),
            Format::getPrivateFormats($seriesName)
        );
    }
    if ($formatType == 'All') {
        $formatNames = Format::getAllFormats();
    }

    echo "<select class=\"inputbox\" name=\"format\" STYLE=\"width: 250px\">\n";
    echo "<option value=\"Unclassified\">- {$formatType} Format Name -</option>\n";
    foreach ($formatNames as $formatName) {
        echo "<option value=\"$formatName\">$formatName</option>\n";
    }
}

function printOrganizerSelect($player_series, $selected)
{
    $page = $_SERVER['PHP_SELF'];
    echo '<center>';
    echo "<form action=\"$page\" method=\"get\">";
    echo '<select class="inputbox" name="series">';
    foreach ($player_series as $series) {
        echo "<option value=\"{$series}\"";
        if ($series == $selected) {
            echo ' selected';
        }
        echo ">{$series}</option>";
    }
    echo '</select>';
    echo '<input class="inputbutton" type="submit" value="Select Series">';
    echo '</form>';
}

function print_warning_if($conditional)
{
    if ($conditional) {
        echo '<span style="color: red;">⚠</span>';
    }
}

function version_number()
{
    return '4.8.8';
}

function version_tagline()
{
    echo 'Gatherling version 4.8.8 ("Fish fingers and custard")';
    // echo 'Gatherling version 4.8.7 ("Step 7: Steal a bagel.")';
    // echo 'Gatherling version 4.8.6.1 ("I\'m gonna steal the declaration of independence.")';
    // echo 'Gatherling version 4.8.6 ("I\'m gonna steal the declaration of independence.")';
    // echo 'Gatherling version 4.8.5 ("That\'s my secret, Captain: I\'m always angry...")';
    // echo 'Gatherling version 4.8.4 ("It doesn\'t look like anything to me.")';
    // echo 'Gatherling version 4.8.3 ("These violent delights have violent ends.")';
    // print "Gatherling version 4.8.2 (\"Zagreus taking time apart. / Zagreus fears the hero heart. / Zagreus seeks the final part. / The reward that he is reaping..\")";
    // print "Gatherling version 4.8.1 (\"Zagreus at the end of days / Zagreus lies all other ways / Zagreus comes when time's a maze / And all of history is weeping.\")";
    // print "Gatherling version 4.8.0 (\"Zagreus sits inside your head / Zagreus lives among the dead / Zagreus sees you in your bed / And eats you when you're sleeping.\")";
  // print "Gatherling version 4.7.0 (\"People assume that time is a strict progression of cause to effect, but actually — from a non-linear, non-subjective viewpoint — it's more like a big ball of wibbly-wobbly... timey-wimey... stuff.\")";
  // print "Gatherling version 4.5.2 (\"People assume that time is a strict progression of cause to effect, but actually — from a non-linear, non-subjective viewpoint — it's more like a big ball of wibbly-wobbly... timey-wimey... stuff.\")";
  // print "Gatherling version 4.0.0 (\"Call me old fashioned, but, if you really wanted peace, couldn't you just STOP FIGHTING?\")";
  // print "Gatherling version 3.3.0 (\"Do not offend the Chair Leg of Truth. It is wise and terrible.\")";
  // print "Gatherling version 2.1.27PK (\"Please give us a simple answer, so that we don't have to think, because if we think, we might find answers that don't fit the way we want the world to be.\")";
  // print "Gatherling version 2.1.26PK (\"The program wasn't designed to alter the past. It was designed to affect the future.\")";
  // print "Gatherling version 2.0.6 (\"We stole the Statue of Liberty! ...  The small one, from Las Vegas.\")";
  // print "Gatherling version 2.0.5 (\"No, that's perfectly normal paranoia. Everyone in the universe gets that.\")";
  // print "Gatherling version 2.0.4 (\"This is no time to talk about time. We don't have the time!\")";
  // print "Gatherling version 2.0.3 (\"Are you hungry? I haven't eaten since later this afternoon.\")";
  // print "Gatherling version 2.0.2 (\"Woah lady, I only speak two languages, English and bad English.\")";
  // print "Gatherling version 2.0.1 (\"Use this to defend yourself. It's a powerful weapon.\")";
  // print "Gatherling version 2.0.0 (\"I'm here to keep you safe, Sam.  I want to help you.\")";
  // print "Gatherling version 1.9.9 (\"You'd think they'd never seen a girl and a cat on a broom before\")";
  // print "Gatherling version 1.9.8 (\"I'm tellin' you, man, every third blink is slower.\")";
  // print "Gatherling version 1.9.7 (\"Try blue, it's the new red!\")";
  // print "Gatherling version 1.9.6 (\"Just relax and let your mind go blank. That shouldn't be too hard for you.\")";
  // print "Gatherling version 1.9.5 (\"The grade that you receive will be your last, WE SWEAR!\")";
  // print "Gatherling version 1.9.4 (\"We're gonna need some more FBI guys, I guess.\")";
  // print "Gatherling version 1.9.3 (\"This is the Ocean, silly, we're not the only two in here.\")";
  // print "Gatherling version 1.9.2 (\"So now you're the boss. You're the King of Bob.\")";
  // print "Gatherling version 1.9.1 (\"It's the United States of Don't Touch That Thing Right in Front of You.\")";
  // print "Gatherling version 1.9 (\"It's funny 'cause the squirrel got dead\")";
}

function redirect($page)
{
    global $CONFIG;
    header("Location: {$CONFIG['base_url']}{$page}");
    exit(0);
}

function parseCards($cards)
{
    if (!is_array($cards)) {
        $cardarr = [];
        $cards = explode("\n", $cards);
    }
    foreach ($cards as $card) {
        // AE Litigation
        $card = normaliseCardName($card);
        if ($card != '') {
            $cardarr[] = $card;
        }
    }

    return $cardarr;
}

function normaliseCardName($card, $tolower = false)
{
    $pattern = ['/é/', '/è/', '/ë/', '/ê/', '/É/', '/È/', '/Ë/', '/Ê/', '/á/', '/à/', '/ä/', '/â/', '/å/', '/Á/', '/À/', '/Ä/', '/Â/', '/Å/', '/ó/', '/ò/', '/ö/', '/ô/', '/Ó/', '/Ò/', '/Ö/', '/Ô/', '/í/', '/ì/', '/ï/', '/î/', '/Í/', '/Ì/', '/Ï/', '/Î/', '/ú/', '/ù/', '/ü/', '/û/', '/Ú/', '/Ù/', '/Ü/', '/Û/', '/ý/', '/ÿ/', '/Ý/', '/ø/', '/Ø/', '/œ/', '/Œ/', '/Æ/', '/AE/', '/ç/', '/Ç/', '/—/', '/−/', '/â€”/', '/’/', '/½/'];
    $replace = ['e', 'e', 'e', 'e', 'E', 'E', 'E', 'E', 'a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O', 'i', 'i', 'i', 'I', 'I', 'I', 'I', 'I', 'u', 'u', 'u', 'u', 'U', 'U', 'U', 'U', 'y', 'y', 'Y', 'o', 'O', 'ae', 'ae', 'Ae', 'Ae', 'c', 'C', '-', '-', '-', "'", '{1/2}'];
    $card = preg_replace($pattern, $replace, $card);
    $card = preg_replace("/\306/", 'AE', $card);
    $card = preg_replace("/ \/\/ /", '/', $card);
    if ($tolower) {
        $card = strtolower($card);
    }

    return $card = trim($card);
}

function parseCardsWithQuantity($cards)
{
    $cards = parseCards($cards);
    $badcards = [];
    $cardarr = [];
    foreach ($cards as $line) {
        $chopped = rtrim($line);
        if (preg_match("/[ \t]*([0-9]+)x?[ \t]+(.*)/i", $chopped, $m)) {
            $qty = $m[1];
            $card = rtrim($m[2]);
            if (isset($cardarr[$card])) {
                $cardarr[$card] += $qty;
            } else {
                $cardarr[$card] = $qty;
            }
        }
    }

    return $cardarr;
}

function print_tooltip($text, $tooltip)
{
    echo "<span class=\"tooltip\" title=\"$tooltip\">$text</span>";
}
