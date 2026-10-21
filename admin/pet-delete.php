<?php
/**
 * PawConnect - Admin Delete Pet Action
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid security token.');
        header('Location: /admin/pets.php');
        exit;
    }

    $petId = (int)($_POST['pet_id'] ?? 0);
    if ($petId > 0) {
        $db = getDB();
        $stmt = $db->prepare("SELECT name FROM pets WHERE id = ?");
        $stmt->execute([$petId]);
        $name = $stmt->fetchColumn();

        if ($name) {
            $del = $db->prepare("DELETE FROM pets WHERE id = ?");
            $del->execute([$petId]);
            set_flash('success', "Pet profile '{$name}' has been deleted.");
        }
    }
}

header('Location: /admin/pets.php');
exit;
