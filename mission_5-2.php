<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>MySQLでデータベースに保存する仕組み</title>
</head>
<body>
  <h3>あなたの願い事は何ですか？</h3>
  <?php
  
    // HTMLのformでundefined noticeが出るのを防ぐために初期値を設定する
    $name = ""; // 名前を代入する変数の初期値
    $comment = ""; // コメントを代入する変数の初期値
    $num = 1; // 投稿番号を代入する変数の初期値は１
    $delete_num = null; // 削除番号を代入する変数の初期値
    $edit_num = null; // 編集する投稿番号を代入する変数の初期値
    $edit_name = ""; // 編集する名前を代入する変数の初期値
    $edit_comment = ""; // 編集するコメントを代入する変数の初期値
    $edit_pass = ""; // 編集するパスワードを代入する変数の初期値
    $date = date("Y-m-d H:i:s"); // date関数で取得した投稿日時を変数に代入する
    $pass = ""; // パスワードを代入する変数の初期値
    
    // データベースへの接続
    $dsn = 'データベース名';
    $user = 'ユーザー名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
    // データベース内にテーブルを作成（もし存在しなかったら）
    $sql = "CREATE TABLE IF NOT EXISTS tb5_2"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name char(32),"
    . "comment TEXT,"
    . "pass char(32)"
    .");";
    $stmt = $pdo->query($sql);
    
    // 条件分岐スタート
    
    if(isset($_POST["delete"]) === true) { # 削除ボタンがクリックされたら -----
      
      # POST受信
      $delete_num = $_POST["delete_num"];
      $pass = $_POST["pass"];
      
      if(!empty($delete_num) && !empty($pass)) { # 空欄では作動しないように
            
        // データ削除
        $id = $_POST["delete_num"]; // 削除する投稿番号
        $sql = 'delete from tb5_2 where id=:id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // 削除後に残っている全データをブラウザに表示
        $sql = 'SELECT * FROM tb5_2';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row) {
          echo $row['id'].',';
          echo $row['name'].',';
          echo $row['comment'].'<br>';
          echo "<hr>";
        }
      } elseif(empty($delete_num) && empty($pass)) { # 空のまま送信された場合
          echo "削除番号とパスワードを入力してください";
        } elseif(empty($delete_num)) {
            echo "削除番号を入力してください";
          } elseif(empty($pass)) {
              echo "パスワードを入力してください";
            }
      $pass = ""; // パスワード初期化（初期化しなければパスワードがformにずっと表示されてしまう）
    } elseif(isset($_POST["edit"]) === true) { # 編集ボタンがクリックされたら -----
    
        // POST受信
        $id = $_POST["edit_num"]; // 変更する投稿番号
        $edit_num = $_POST["edit_num"]; // 変更する投稿番号
        $pass = $_POST["pass"]; // その投稿番号の元のパスワード
        
        if(!empty($edit_num) && !empty($pass)) { # 空欄では作動しないように
          
          // 該当の番号のデータをセット
          $sql = 'SELECT * FROM tb5_2 WHERE id=:id';
          $stmt = $pdo->prepare($sql);
          $stmt->bindParam(':id', $id, PDO::PARAM_INT);
          $stmt->execute();
          $results = $stmt->fetchAll();
          foreach ($results as $row) {
            $edit_num = $row['id'];
            $edit_name = $row['name'];
            $edit_comment = $row['comment'];
            $edit_pass = $row['pass'];
          }
          //$edit_num = $_POST["edit_num"];
        } elseif(empty($edit_num) && empty($pass)) { # 空のまま送信された場合
            echo "編集番号とパスワードを入力してください";
          } elseif(empty($edit_num)) {
              echo "編集番号を入力してください";
            } elseif(empty($pass)) {
                echo "パスワードを入力してください";
              }
        $pass = ""; // パスワード初期化
      } elseif(isset($_POST["submit"]) === true) { # 送信ボタンがクリックされたら -----
    
          # POST受信 
          $id = $_POST["edit_num"];
          $name = $_POST["name"];
          $comment = $_POST["comment"];
          $pass = $_POST["pass"];
          $edit_num = $_POST["edit_num"]; // 編集番号をhiddenで送ってます
        
          if(!empty($name) && !empty($comment) && !empty($pass)) { # 空欄では作動しないように
        
            if(!empty($edit_num)) { # 編集だったら
  
              // データ更新
              $sql = 'UPDATE tb5_2 SET name=:name,comment=:comment,pass=:pass WHERE id=:id';
              $stmt = $pdo->prepare($sql);
              $stmt->bindParam(':name', $name, PDO::PARAM_STR);
              $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
              $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
              $stmt->bindParam(':id', $id, PDO::PARAM_INT);
              $stmt->execute();
    
              // 編集後に全データをブラウザに表示
              $sql = 'SELECT * FROM tb5_2';
              $stmt = $pdo->query($sql);
              $results = $stmt->fetchAll();
              foreach ($results as $row) {
                echo $row['id'].',';
                echo $row['name'].',';
                echo $row['comment'].'<br>';
                echo "<hr>";
              }
            }  else { # 新規投稿だったら
                 
                 // テーブル内にデータ挿入
                 $sql = $pdo -> prepare("INSERT INTO tb5_2 (name, comment, pass) VALUES (:name, :comment, :pass)");
                 $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                 $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                 $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
                 $name = $_POST["name"];
                 $comment = $_POST["comment"];
                 $pass = $_POST["pass"];
                 $sql -> execute();
    
                 // データをブラウザに表示
                 $sql = 'SELECT * FROM tb5_2';
                 $stmt = $pdo->query($sql);
                 $results = $stmt->fetchAll();
                 foreach ($results as $row) {
                   echo $row['id'].',';
                   echo $row['name'].',';
                   echo $row['comment'].'<br>';
                   echo "<hr>";
                 }
                 echo $name."さんの願い事が叶いますように<br>";
               }
          } elseif(empty($name) && empty($comment) && empty($pass)) { # 空のまま送信された場合
              echo "名前と願い事とパスワードを入力してください";
            } elseif(empty($name) && empty($comment)) {
                echo "名前と願い事を入力してください";
              } elseif(empty($comment) && empty($pass)) {
                  echo "願い事とパスワードを入力してください";
                } elseif(empty($name) && empty($pass)) {
                    echo "名前とパスワードを入力してください";
                  } elseif(empty($name)) {
                      echo "名前を入力してください";
                    } elseif(empty($pass)) {
                        echo "パスワードを入力してください";
                      } elseif(empty($pass)) {
                          echo "願い事を入力してください";
                        }
          $pass = ""; // パスワード初期化
    } elseif(isset($_POST["see"]) === true) { # 閲覧用ボタンがクリックされたら -----
        // データをブラウザに表示
        $sql = 'SELECT * FROM tb5_2';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row) {
          echo $row['id'].',';
          echo $row['name'].',';
          echo $row['comment'].'<br>';
          echo "<hr>";
        }
      }
  ?>
   <hr>

  <!--POST送信-->

  <!--入力フォーム-->
  <form action="" method="post">
    <h5>【入力フォーム】</h5>
    お名前　　：<input type="str" name="name" placeholder="お名前を入力してください" value="<?php echo $edit_name; ?>"><br>
    願い事　　：<input type="str" name="comment" placeholder="願い事は何ですか" value="<?php echo $edit_comment; ?>"><br>
    パスワード：<input type="password" name="pass" placeholder="パスワードを入力してください" value="<?php echo $edit_pass; ?>">
    <input type="hidden" name="edit_num" value="<?php echo $edit_num; ?>"><br>
    <input type="submit" name="submit" value="送信"><br>
  </form>

  <!--削除番号指定用フォーム-->
  <form action="" method="post">
    <br>
    <h5>【削除番号指定フォーム】</h5>
    削除対象番号：<input type="number" name="delete_num" placeholder="数字を入力してください"><br>
    パスワード　：<input type="password" name="pass" placeholder="パスワードを入力してください" value="<?php echo $pass; ?>"><br>
    <input type="submit" name="delete" value="削除"><br>
  </form>
  
  <!--編集番号指定用フォーム--> <!--このフォームで編集できるようにする？そしたら元の投稿の表示がされないよね-->
  <form action="" method="post">
    <br>
    <h5>【編集番号指定フォーム】</h5>
    編集対象番号：<input type="number" name="edit_num" placeholder="数字を入力してください"><br>
    パスワード　：<input type="password" name="pass" placeholder="パスワードを入力してください" value="<?php echo $pass; ?>"><br>
    <input type="submit" name="edit" value="編集"><br>
  </form>
  
  <!--閲覧用フォーム-->
  <form action="" method="post">
    <br><input type="submit" name="see" value="みんなの願い事を見る"><br>
  </form>
  
  <hr>
 </body>
 </html>