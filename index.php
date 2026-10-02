<?php
use Carbon\Carbon;

// Composer laadt Carbon en de databaseverbinding.
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/db.php';

// Laat Carbon de tijdsaanduidingen in het Nederlands tonen.
Carbon::setLocale('nl');

// Variabelen voor fouten en berichten
$errors = [];
$successMessage = '';
$name = '';
$email = '';
$comment = '';

// Check of het formulier is verzonden
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Invoer ophalen en lege ruimtes verwijderen
    $name = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
    $email = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
    $comment = trim(is_string($_POST['comment'] ?? null) ? $_POST['comment'] : '');

    // 2. Server-side controle (PHP)
    if (empty($name)) {
        $errors['name'] = 'Vul een naam in.';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'De naam mag maximaal 100 tekens bevatten.';
    }

    if (empty($email)) {
        $errors['email'] = 'Vul een e-mailadres in.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Vul een geldig e-mailadres in.';
    } elseif (strlen($email) > 254) {
        $errors['email'] = 'Het e-mailadres mag maximaal 254 tekens bevatten.';
    }

    if (empty($comment)) {
        $errors['comment'] = 'Vul een reactie in.';
    } elseif (strlen($comment) > 5000) {
        $errors['comment'] = 'Een reactie mag maximaal 5000 tekens bevatten.';
    }

    // 3. Opslaan in de database als er geen fouten zijn
    if (empty($errors)) {
        $sql = "INSERT INTO comments (name, email, comment, created_at) VALUES (:name, :email, :comment, NOW())";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':comment' => $comment
        ]);

        $successMessage = 'Je reactie is geplaatst!';

        // Velden weer leegmaken
        $name = '';
        $email = '';
        $comment = '';
    }
}

// Haal alle reacties op uit de database (nieuwste bovenaan)
$stmt = $pdo->query("SELECT * FROM comments ORDER BY created_at DESC");
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#17231f">
    <title>Frame Notes | Video & reacties</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
            --ink: #17231f;
            --paper: #f1f3ec;
            --white: #fff;
            --muted: #66716a;
            --line: #d8ddd4;
            --acid: #d8f36a;
            --coral: #ef6146;
            --error: #a32e23;
            --display: 'Space Grotesk', sans-serif;
            --body: 'DM Sans', sans-serif;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            min-width: 320px;
            margin: 0;
            color: var(--ink);
            background-color: var(--paper);
            background-image: radial-gradient(#cbd1c8 0.65px, transparent 0.65px);
            background-size: 18px 18px;
            font-family: var(--body);
            -webkit-font-smoothing: antialiased;
        }

        .masthead {
            color: var(--white);
            background: var(--ink);
            border-bottom: 4px solid var(--acid);
        }

        .masthead-inner {
            display: flex;
            width: min(1080px, calc(100% - 48px));
            min-height: 76px;
            align-items: center;
            justify-content: space-between;
            margin: 0 auto;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: inherit;
            text-decoration: none;
        }

        .brand-symbol {
            display: grid;
            width: 34px;
            aspect-ratio: 1;
            place-items: center;
            color: var(--ink);
            background: var(--acid);
            font-family: var(--display);
            font-size: 19px;
            font-weight: 700;
        }

        .brand-name {
            font-family: var(--display);
            font-size: 16px;
            font-weight: 700;
        }

        .brand-slash {
            color: var(--acid);
        }

        .header-note,
        .video-kicker,
        .eyebrow {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header-note {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #c4cec6;
        }

        .live-dot {
            width: 8px;
            aspect-ratio: 1;
            border-radius: 50%;
            background: var(--coral);
            box-shadow: 0 0 0 4px rgb(239 97 70 / 16%);
        }

        .page-shell {
            width: min(1080px, calc(100% - 48px));
            margin: 0 auto;
            padding: 54px 0 88px;
        }

        .video-section,
        .discussion {
            animation: arrive 550ms ease both;
        }

        .discussion {
            animation-delay: 100ms;
        }

        .video-kicker {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            color: var(--muted);
        }

        .video-kicker span:first-child::before {
            display: inline-block;
            width: 8px;
            height: 8px;
            margin-right: 9px;
            background: var(--coral);
            content: '';
        }

        h1,
        h2,
        h3,
        p {
            margin-top: 0;
        }

        h1 {
            margin: 18px 0 4px;
            font-family: var(--display);
            font-size: 48px;
            line-height: 1.05;
        }

        h1 span {
            color: var(--coral);
        }

        .intro {
            margin-bottom: 24px;
            color: var(--muted);
            font-size: 16px;
        }

        .video-frame {
            overflow: hidden;
            aspect-ratio: 16 / 9;
            padding: 7px;
            background: var(--white);
            border: 1px solid #dfe3dc;
            box-shadow: 0 18px 44px rgb(23 35 31 / 12%);
        }

        .video-frame video {
            display: block;
            width: 100%;
            height: 100%;
            background: #101412;
            object-fit: cover;
        }

        .discussion {
            margin-top: 52px;
            padding-top: 28px;
            border-top: 1px solid #bdc5bb;
        }

        .discussion-heading {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 26px;
        }

        .eyebrow {
            margin-bottom: 8px;
            color: #4d6940;
        }

        h2 {
            margin-bottom: 0;
            font-family: var(--display);
            font-size: 34px;
            line-height: 1.1;
        }

        .comment-count {
            padding: 7px 10px;
            color: var(--ink);
            background: var(--acid);
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .discussion-grid {
            display: grid;
            grid-template-columns: minmax(260px, 0.82fr) minmax(0, 1.18fr);
            gap: 52px;
            align-items: start;
        }

        .compose {
            padding: 25px;
            background: var(--white);
            border-top: 4px solid var(--coral);
            box-shadow: 0 8px 24px rgb(23 35 31 / 6%);
        }

        h3 {
            margin-bottom: 7px;
            font-family: var(--display);
            font-size: 21px;
        }

        .compose-copy {
            margin-bottom: 20px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.55;
        }

        .form-field {
            margin-top: 15px;
        }

        .form-field label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 700;
        }

        .form-field input,
        .form-field textarea {
            display: block;
            width: 100%;
            padding: 11px 12px;
            color: var(--ink);
            background: #f9faf6;
            border: 1px solid var(--line);
            border-radius: 0;
            font: inherit;
            font-size: 14px;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }

        .form-field textarea {
            min-height: 112px;
            resize: vertical;
        }

        .form-field input:focus,
        .form-field textarea:focus {
            border-color: #557b36;
            outline: 0;
            box-shadow: 0 0 0 3px rgb(216 243 106 / 55%);
        }

        .form-field input[aria-invalid="true"],
        .form-field textarea[aria-invalid="true"] {
            border-color: var(--error);
        }

        .field-error {
            margin: 6px 0 0;
            color: var(--error);
            font-size: 12px;
        }

        .submit-button {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
            padding: 13px 15px;
            color: var(--white);
            background: var(--ink);
            border: 0;
            font: inherit;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 150ms ease, color 150ms ease;
        }

        .submit-button:hover {
            color: var(--ink);
            background: var(--acid);
        }

        .submit-button span {
            color: var(--acid);
            font-size: 20px;
            line-height: 1;
            transition: color 150ms ease, transform 150ms ease;
        }

        .submit-button:hover span {
            color: var(--ink);
            transform: translateX(3px);
        }

        .notice {
            margin-bottom: 17px;
            padding: 11px 12px;
            font-size: 13px;
        }

        .notice-success {
            color: #244d2d;
            background: #e5f4d9;
            border-left: 3px solid #6d9b45;
        }

        .comments-list {
            border-top: 1px solid var(--line);
        }

        .comment-item {
            padding: 18px 0 19px;
            border-bottom: 1px solid var(--line);
        }

        .comment-meta {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 14px;
        }

        .comment-meta strong {
            font-family: var(--display);
            font-size: 15px;
        }

        .comment-time {
            color: var(--muted);
            font-size: 12px;
            white-space: nowrap;
        }

        .comment-text {
            margin: 9px 0 0;
            color: #39443d;
            font-size: 14px;
            line-height: 1.65;
            overflow-wrap: anywhere;
            white-space: pre-wrap;
        }

        .empty-state {
            padding: 20px 0;
            color: var(--muted);
            font-size: 14px;
        }

        @keyframes arrive {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 720px) {
            .discussion-grid {
                grid-template-columns: 1fr;
                gap: 34px;
            }

            .compose {
                max-width: none;
            }
        }

        @media (max-width: 520px) {
            .masthead-inner,
            .page-shell {
                width: calc(100% - 32px);
            }

            .masthead-inner {
                min-height: 66px;
            }

            .header-note {
                font-size: 9px;
            }

            .page-shell {
                padding-top: 36px;
                padding-bottom: 56px;
            }

            .video-kicker {
                font-size: 9px;
            }

            h1 {
                font-size: 36px;
            }

            .intro {
                font-size: 14px;
            }

            .video-frame {
                padding: 4px;
            }

            .discussion {
                margin-top: 38px;
                padding-top: 22px;
            }

            h2 {
                font-size: 29px;
            }

            .compose {
                padding: 20px 17px;
            }

            .comment-meta {
                align-items: flex-start;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <header class="masthead">
        <div class="masthead-inner">
            <a class="brand" href="index.php" aria-label="Frame Notes home">
                <span class="brand-symbol" aria-hidden="true">F</span>
                <span class="brand-name">FRAME <span class="brand-slash">/</span> NOTES</span>
            </a>
            <span class="header-note"><span class="live-dot" aria-hidden="true"></span> Video journal · open gesprek</span>
        </div>
    </header>

    <main class="page-shell">
        <section class="video-section" aria-labelledby="video-title">
            <div class="video-kicker">
                <span>Nu op het scherm</span>
                <span>Short film · 01</span>
            </div>
            <h1 id="video-title">Bekijk de video<span>.</span></h1>
            <p class="intro">Een korte film. Een plek voor jouw perspectief.</p>
            <div class="video-frame">
                <video controls preload="metadata">
                    <source src="https://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4">
                    Je browser ondersteunt deze video niet.
                </video>
            </div>
        </section>

        <section class="discussion" aria-labelledby="discussion-title">
            <div class="discussion-heading">
                <div>
                    <p class="eyebrow">Na de credits</p>
                    <h2 id="discussion-title">De reacties</h2>
                </div>
                <span class="comment-count"><?= count($comments) ?> <?= count($comments) === 1 ? 'reactie' : 'reacties' ?></span>
            </div>

            <div class="discussion-grid">
                <section class="compose" aria-labelledby="compose-title">
                    <h3 id="compose-title">Wat bleef bij je?</h3>
                    <p class="compose-copy">Deel je gedachte met iedereen die de film heeft gezien.</p>

                    <?php if (!empty($successMessage)): ?>
                        <div class="notice notice-success" role="status"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>

                    <form action="index.php" method="POST">
                        <div class="form-field">
                            <label for="name">Naam</label>
                            <input id="name" type="text" name="name" maxlength="100" value="<?= htmlspecialchars($name) ?>" autocomplete="name" required aria-invalid="<?= isset($errors['name']) ? 'true' : 'false' ?>" <?= isset($errors['name']) ? 'aria-describedby="name-error"' : '' ?>>
                            <?php if (isset($errors['name'])): ?>
                                <p class="field-error" id="name-error" role="alert"><?= htmlspecialchars($errors['name']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="form-field">
                            <label for="email">E-mailadres</label>
                            <input id="email" type="email" name="email" maxlength="254" value="<?= htmlspecialchars($email) ?>" autocomplete="email" required aria-invalid="<?= isset($errors['email']) ? 'true' : 'false' ?>" <?= isset($errors['email']) ? 'aria-describedby="email-error"' : '' ?>>
                            <?php if (isset($errors['email'])): ?>
                                <p class="field-error" id="email-error" role="alert"><?= htmlspecialchars($errors['email']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="form-field">
                            <label for="comment">Reactie</label>
                            <textarea id="comment" name="comment" rows="4" maxlength="5000" required aria-invalid="<?= isset($errors['comment']) ? 'true' : 'false' ?>" <?= isset($errors['comment']) ? 'aria-describedby="comment-error"' : '' ?>><?= htmlspecialchars($comment) ?></textarea>
                            <?php if (isset($errors['comment'])): ?>
                                <p class="field-error" id="comment-error" role="alert"><?= htmlspecialchars($errors['comment']) ?></p>
                            <?php endif; ?>
                        </div>

                        <button class="submit-button" type="submit">Plaats reactie <span aria-hidden="true">→</span></button>
                    </form>
                </section>

                <div class="comments-column">
                    <?php if (empty($comments)): ?>
                        <p class="empty-state">Nog geen reacties. Open het gesprek.</p>
                    <?php else: ?>
                        <div class="comments-list">
                            <?php foreach ($comments as $item): ?>
                                <article class="comment-item">
                                    <div class="comment-meta">
                                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                                        <span class="comment-time"><?= htmlspecialchars(Carbon::parse($item['created_at'])->diffForHumans()) ?></span>
                                    </div>
                                    <p class="comment-text"><?= htmlspecialchars($item['comment']) ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</body>
</html>