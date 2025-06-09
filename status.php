<?php
function formatBytes($bytes, $precision = 2) {
    if (!is_numeric($bytes) || $bytes <= 0) return 'صفر';
    $units = ['B','KB','MB','GB','TB'];
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
}

// شروع کد jdf.php
function gregorian_to_jalali($gy, $gm, $gd, $mod='') {
    $g_d_m = [0,31,59,90,120,151,181,212,243,273,304,334];
    if ($gy > 1600) {$jy=979;$gy-=1600;} else {$jy=0;$gy-=621;}
    $gy2 = ($gm > 2)? ($gy + 1) : $gy;
    $days = (365 * $gy) + (int)(($gy2 + 3) / 4) - (int)(($gy2 + 99) / 100)
        + (int)(($gy2 + 399) / 400) - 80 + $gd + $g_d_m[$gm -1];
    $jy += 33 * (int)($days / 12053);
    $days %= 12053;
    $jy += 4 * (int)($days / 1461);
    $days %= 1461;
    if ($days > 365) {$jy += (int)(($days - 1) / 365);$days = ($days -1)%365;}
    $jm = ($days < 186)? 1 + (int)($days / 31) : 7 + (int)(($days - 186) / 30);
    $jd = 1 + (($days < 186)? ($days % 31) : (($days - 186) % 30));
    return ($mod === '') ? [$jy, $jm, $jd] : "$jy$mod$jm$mod$jd";
}
// تابع تبدیل تاریخ میلادی به نام روز هفته فارسی
function getPersianWeekday($gy, $gm, $gd) {
    $weekdays = ['یکشنبه','دوشنبه','سه‌شنبه','چهارشنبه','پنجشنبه','جمعه','شنبه'];
    $ts = mktime(0, 0, 0, $gm, $gd, $gy);
    return $weekdays[date('w', $ts)];
}
// پایان کد jdf.php
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل اشتراک X-UI</title>
    <style>
        body {
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            transition: background-image 1s ease-in-out;
            color: #fff;
            font-family: Tahoma, sans-serif;
            padding: 20px;
            text-align: center;
        }
        h1 {
            background-color: rgba(0,0,0,0.6);
            display: inline-block;
            padding: 10px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            color: #00ffff;
            font-size: 24px;
        }
        form input {
            padding: 10px;
            width: 90%;
            max-width: 300px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
        }
        form button {
            padding: 10px 20px;
            background: #00ffff;
            color: #000;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.3s;
        }
        .card {
            background: rgba(0, 0, 0, 0.65);
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(0,0,0,0.6);
            padding: 20px;
            max-width: 90%;
            margin: 30px auto;
            text-align: right;
            backdrop-filter: blur(6px);
        }
        .item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #444;
            font-size: 15px;
        }
        .item:last-child {
            border: none;
        }
        .label { font-weight: bold; color: #ccc; }
        .value { color: #fff; }
        .progress {
            width: 100%;
            background: #333;
            border-radius: 8px;
            overflow: hidden;
            margin: 15px 0;
        }
        .progress-bar {
            height: 20px;
            transition: width 0.5s;
        }
        .safe { background: #4caf50; }
        .warning { background: #ff9800; }
        .danger { background: #f44336; }
        @media screen and (max-width: 480px) {
            h1 { font-size: 20px; }
            .item { flex-direction: column; text-align: left; }
            .label, .value { padding: 2px 0; }
        }
    </style>
    <script>
        const backgrounds = [
            'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1920&q=80',
            'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1920&q=80',
            'https://images.unsplash.com/photo-1506765515384-028b60a970df?auto=format&fit=crop&w=1920&q=80'
        ];
        function changeBackground() {
            const image = backgrounds[Math.floor(Math.random() * backgrounds.length)];
            document.body.style.backgroundImage = `url('${image}')`;
        }
        window.onload = () => {
            changeBackground();
            setInterval(changeBackground, 300000);
        };
    </script>
</head>
<body>
<h1>بررسی وضعیت اشتراک شما</h1>
<form method="get">
    <input type="text" name="email" placeholder="نام کاربر (email)" required>
    <br><button type="submit">نمایش</button>
</form>
<?php
if (isset($_GET['email'])) {
    $email = $_GET['email'];
    $dbPath = '/etc/x-ui/x-ui.db';
    try {
        $pdo = new PDO("sqlite:$dbPath");
        $stmt = $pdo->prepare("SELECT up, down, expiry_time, total FROM client_traffics WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $up = $row['up'];
            $down = $row['down'];
            $used = $up + $down;
            $total = $row['total'];
            $left = $total > 0 ? $total - $used : 'نامحدود';
            $usagePercent = ($total > 0) ? round(($used / $total) * 100) : 0;
            $progressClass = $usagePercent < 70 ? 'safe' : ($usagePercent < 90 ? 'warning' : 'danger');

            $expiry = $row['expiry_time'];
            if ($expiry > 0) {
                $ts = $expiry / 1000;
                list($gy, $gm, $gd) = explode('-', date("Y-m-d", $ts));
                $shamsi = gregorian_to_jalali($gy, $gm, $gd, '/');
                $weekday = getPersianWeekday($gy, $gm, $gd);
                $shamsi = "$weekday $shamsi";
                $remainingSeconds = $ts - time();
                $days = floor($remainingSeconds / 86400);
                $hours = floor(($remainingSeconds % 86400) / 3600);
                $remainingText = ($remainingSeconds > 0) ? sprintf('%d روز و %d ساعت باقی‌مانده', $days, $hours) : 'منقضی شده';
            } else {
                $shamsi = 'تعیین نشده';
                $remainingText = 'نامشخص';
            }

            echo "<div class='card'>
                    <h2>اطلاعات اشتراک: <span style='color:#fff;'>$email</span></h2>
                    <div class='item'><div class='label'>حجم کل:</div><div class='value'>" . formatBytes($total) . "</div></div>
                    <div class='item'><div class='label'>حجم مصرف‌شده:</div><div class='value'>" . formatBytes($used) . "</div></div>
                    <div class='item'><div class='label'>حجم باقی‌مانده:</div><div class='value'>" . (is_numeric($left) ? formatBytes($left) : $left) . "</div></div>
                    <div class='item'><div class='label'>تاریخ انقضا (شمسی):</div><div class='value'>$shamsi</div></div>
                    <div class='item'><div class='label'>زمان باقی‌مانده:</div><div class='value'>$remainingText</div></div>
                    <div class='item' style='flex-direction: column;'>
                        <div class='label'>درصد مصرف:</div>
                        <div class='progress'><div class='progress-bar $progressClass' style='width: {$usagePercent}%;'></div></div>
                        <div class='value'>$usagePercent%</div>
                    </div>
                  </div>";
        } else {
            echo "<p class='error'>❌ کاربر پیدا نشد یا هنوز مصرفی ثبت نشده.</p>";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>خطا در اتصال: " . $e->getMessage() . "</p>";
    }
}
?>
<footer style="margin-top: 40px; font-size: 13px; color: black; font-family: 'Verdana', sans-serif; font-weight: bold;">
    Developed with ❤️ by Mr.Ali — 2025 | <a href="https://t.me/iCnii" target="_blank" style="color:#00ffff;text-decoration:none;">
        <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/telegram.svg" alt="Telegram" style="width:16px; vertical-align:middle; margin-left:4px; filter: brightness(0) saturate(100%) invert(62%) sepia(47%) saturate(750%) hue-rotate(155deg) brightness(95%) contrast(95%)"> Telegram
    </a>
</footer>
</body>
</html>
