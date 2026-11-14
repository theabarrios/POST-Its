<?php
session_start();

require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "GET") {

    if (!isset($_SESSION["visits"])) {
        $_SESSION["visits"] = 1;
    } else {
        $_SESSION["visits"]++;
    }
}

$name = "";
$message = "";
$error = "";
$search = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["name"])) {
        $name = trim($_POST["name"]);
    }

    if (isset($_POST["message"])) {
        $message = trim($_POST["message"]);
    }

    if ($name === "" || $message === "") {

        $error = "Please enter both your name and message.";

    } else {

        if (isset($_POST["remember"])) {
            setcookie("saved_name", $name, time() + (86400 * 30), "/");
        }

        $sql = "INSERT INTO messages (author, body)
                VALUES (:author, :body)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":author" => $name,
            ":body" => $message
        ]);

        header("Location: index.php");
        exit;
    }
}

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}


$sql = "SELECT * FROM messages
        ORDER BY created_at DESC";

$stmt = $pdo->query($sql);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);


$searchResults = [];

if ($search !== "") {

    $sql = "SELECT * FROM messages
            WHERE body ILIKE :search
            ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":search" => "%" . $search . "%"
    ]);

    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$savedName = "";

if (isset($_COOKIE["saved_name"])) {
    $savedName = $_COOKIE["saved_name"];
}

if ($error !== "") {
    $savedName = $name;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POST-Its</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            color: #352719;

            background-color: #a56736;
            border: 18px solid #d9a881;

            display: flex;
            flex-direction: column;
        }

        header {
            background-color: #fff1a8;
            width: fit-content;
            max-width: 90%;
            margin: 40px auto 50px;
            padding: 25px 45px;

            text-align: center;

            border: 1px solid #e4d17c;
            box-shadow: 4px 6px 10px rgba(0, 0, 0, 0.25);
            transform: rotate(-1deg);
        }

        header h1 {
            font-size: 2.5rem;
            margin: 0 0 10px;
            color: #63452a;
        }

        header p {
            margin: 8px 0;
            color: #755638;
        }

        main {
            width: 85%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
            flex: 1;
        }

        section {
            margin-bottom: 40px;
        }

        section h2 {
            color: #fff0d6;
            border-bottom: 2px solid #d9a881;
            padding-bottom: 10px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            max-width: 500px;
            padding: 10px;
            border: 2px solid #d9a881;
            border-radius: 5px;
            background-color: #fffaf0;
            font-family: inherit;
            font-size: 1rem;
        }

        #postmessage form {
            background-color: #f2ecd3;
            padding: 25px;
            border: 2px solid #dbac91;
            border-radius: 8px;
        }

        #missingparam {
            display: inline-block;
            padding: 10px;
            border-radius: 5px;
            background-color: #ffc9c9;
            color: red;
        }

        .searchres {
            color: #fffaf0;
            text-align: center;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        input[type="checkbox"] {
            accent-color: #80502e;
        }

        button {
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            background-color: #d9a881;
            color: #352719;
            font-size: 1rem;
            cursor: pointer;
        }

        button:hover {
            background-color: #f0c9a5;
        }

        a {
            color: #fff1a8;
        }

        a:hover {
            color: white;
        }

        article {
            background-color: #fff4d6;
            padding: 18px;
            margin-bottom: 20px;
            border: 2px solid #d9a881;
            border-radius: 4px;
            box-shadow: 2px 3px 5px rgba(0, 0, 0, 0.15);
            overflow-wrap: anywhere;
        }

        article h3 {
            margin-top: 0;
            color: #80502e;
        }

        article p {
            line-height: 1.6;
        }

        article small {
            color: #80684f;
        }

        footer {
            text-align: center;
            padding: 15px;
            background-color: #704323;
            color: #fff1d6;
        }

        @media (max-width: 768px) {
            body {
                border-width: 10px;
            }

            main {
                width: 100%;
                padding: 10px;
            }

            header {
                padding: 20px;
            }

            header h1 {
                font-size: 2rem;
            }

            input[type="text"],
            textarea {
                max-width: 100%;
            }
        }
    </style>
</head>

<body>

    <header>

        <h1>POST-Its</h1>
        <p>The message board for all your random thoughts :)</p>

        <p>
            You've visited
            <?= htmlspecialchars((string)$_SESSION["visits"], ENT_QUOTES, 'UTF-8') ?>
            times!
        </p>

    </header>

    <main>

        <section id="searchmessage">
            <h2>Search Messages</h2>

            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search messages..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit">Search</button>
                <button type="button" id="clearsearch">Clear Search</button>
                <script>
                    const button = document.getElementById("clearsearch");
                    button.addEventListener('click', function(){
                        window.location.href = "index.php";
                    });
                </script>
            </form>
        </section>

        <section id="searchresults">
            <h2>Search Results</h2>
            <?php if ($search !== ""): ?>
                <?php if (count($searchResults) > 0): ?>
                    <?php foreach ($searchResults as $row): ?>
                        <article>
                            <h3>
                                <?= htmlspecialchars($row["author"], ENT_QUOTES, 'UTF-8') ?>
                            </h3>

                            <p>
                                <?= nl2br(htmlspecialchars($row["body"], ENT_QUOTES, 'UTF-8')) ?>
                            </p>

                            <small>
                                <?= htmlspecialchars($row["created_at"], ENT_QUOTES, 'UTF-8') ?>
                            </small>
                        </article>
                    <?php endforeach; ?>

                <?php else: ?>
                    <p class="searchres">No matching messages found :( </p>
                <?php endif; ?>

            <?php else: ?>
                <p class="searchres">Enter a keyword to search for messages!</p>
            <?php endif; ?>
        </section>

        <section id="postmessage">
            <h2>Post a Message</h2>

            <?php if ($error !== ""): ?>
                <p id="missingparam">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($savedName, ENT_QUOTES, 'UTF-8') ?>" required>

                <br><br>

                <label for="message">Message:</label>

                <textarea id="message" name="message" required></textarea>

                <br><br>

                <input type="checkbox" id="remember" name="remember">

                <label for="remember">Remember my name</label>

                <br><br>

                <button type="submit">POST IT</button>
            </form>
        </section>

        <section id="postedmessages">
            <h2>Everybody's POST-its</h2>
            <?php if (count($messages) > 0): ?>
                <?php foreach ($messages as $row): ?>
                    <article>
                        <h3>
                            <?= htmlspecialchars($row["author"], ENT_QUOTES, 'UTF-8') ?>
                        </h3>

                        <p>
                            <?= nl2br(htmlspecialchars($row["body"], ENT_QUOTES, 'UTF-8')) ?>
                        </p>

                        <small>
                            <?= htmlspecialchars($row["created_at"], ENT_QUOTES, 'UTF-8') ?>
                        </small>
                    </article>

                <?php endforeach; ?>

            <?php else: ?>
                <p>No messages found.</p>
            <?php endif; ?>
        </section>

    </main>

    <footer>
        <small>@theabarrios on github</small>
    </footer>

</body>
</html>
