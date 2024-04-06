<?php
set_time_limit(0);
//データベースの接続情報
$host = '';
$db = '';
$user = '';
$pass = '';
//接続情報を保存ファイルから読み込む
include '../../private/config.php';

$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
ini_set('memory_limit', '256M');

$s = function ($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
};

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>武士チャット</title>
    <link rel="stylesheet" href="index.css">
</head>

<body>
    <header>
        <h1>武士チャット</h1>
    </header>
    <main>
        <div id="chat-mainbox">
            <?php
            try {
                $pdo = new PDO($dsn, $user, $pass);
                echo "<div id='scroller'>\n";
                $i = 0;
                foreach ($pdo->query('SELECT * FROM bushichat ORDER BY created_at DESC LIMIT 15') as $row) {
                    if ($i % 2 == 0) {
                        echo <<<___EOF___
                    <div class="text-area right-side">
                        <p> {$s($row['text'])} </p>
                    </div>
                ___EOF___;
                    } else {
                        echo <<<___EOF___
                    <div class="text-area left-side">
                        <p> {$s($row['text'])} </p>
                    </div>
                ___EOF___;
                    }
                    $i++;
                }
                echo "</div>\n";
            } catch (PDOException $e) {
                echo "<p>データベースの接続に失敗しました。</p>";
                echo "<p>管理者にお問い合わせください。</p>";
                die();
            }
            ?>
            <div class="input-form">
                <p>入力:</p>
                <textarea name="text" id="chat-box"></textarea>
                <button type="button" onclick="sendmessage()" class="submitButton">送信</button>
            </div>

        </div>
        <section class="info">
            <h2>武士チャットとは</h2>
            <p>武士チャットは、武士になりきって交流するサイトです</p>
            <p>入力した文章が武士語に変換されるので、</p>
            <p>誰でも簡単に参加できます！</p>
            <p>不適切な文章を送信すると</p>
            <p>武士が切腹してしまうことがあるのでご注意を!</p>
            <p>(送信には多少時間がかかります)</p>
        </section>
    </main>

    <script src="/socket.io/socket.io.js"></script>
    <script>
        // 蓋絵を開ける関数
        function openCurtain(){
            // 蓋絵要素の存在確認
            const curtain = document.getElementById('curtain');
            if(curtain){
                // 蓋絵要素が存在する場合、削除
                curtain.remove();
            }
        }

        // 蓋絵で隠す関数
        function closeCurtain(){
            // 蓋絵要素の存在確認
            const curtain = document.getElementById('curtain');
            if(!curtain){
                // 蓋絵要素が存在しない場合、作成
                const newDiv = document.createElement("div");
                newDiv.id = 'curtain';
                newDiv.className = 'curtain';
                document.body.appendChild(newDiv);
            }
        }

        const scroller = document.getElementById('scroller');
        scroller.scrollTop = scroller.scrollHeight - scroller.clientHeight;

        var fullDomain = window.location.origin;
        const socket = io(fullDomain);

        // 接続成功
        socket.on('connect', () => {
            console.log('Connected to server');
            // メッセージ送信
            console.log("接続は成功しました。");
        });

        // 接続切断
        socket.on('disconnect', () => {
            console.log('Disconnected from server');
        });

        // 接続エラー
        socket.on('connect_error', (error) => {
            console.log('Connection error:', error);
        });

        // 再接続
        socket.on('reconnect', (attemptNumber) => {
            console.log(`Reconnected after ${attemptNumber} attempts`);
        });

        function sendmessage() {
            closeCurtain();
            const text = document.getElementById('chat-box').value;
            document.getElementById('chat-box').value = '';
            if (text == '') return;
            socket.emit('sendmessage', text);
        }

        socket.on('sendmessage', (data) => {
            openCurtain();
            const parentElement = document.getElementById('scroller');
            const firstChild = parentElement.firstElementChild;
            const newDiv = document.createElement("div");
            if (firstChild && firstChild.classList.contains('left-side')) {
                newDiv.className = 'text-area right-side';
            } else {
                newDiv.className = 'text-area left-side';
            }
            const newP = document.createElement("p");
            const newContent = document.createTextNode(data);
            newP.appendChild(newContent);
            newDiv.appendChild(newP);
            parentElement.insertBefore(newDiv, firstChild);
            scroller.scrollTop = scroller.scrollHeight - scroller.clientHeight;
        });
    </script>

</body>

</html>
