<?php

function artifact_check($pdo, $user_id)
{
    $stmt = $pdo->prepare('
        DECLARE _artifactDate DATETIME;
        DECLARE _foundDate DATETIME DEFAULT "0000-00-00 00:00:00";

        SELECT updated_time INTO _artifactDate
        FROM artifact_location
        LIMIT 1;

        SELECT time INTO _foundDate
        FROM artifacts_found
        WHERE user_id = :user_id
        LIMIT 1;

        SELECT _artifactDate, _foundDate, (_foundDate > _artifactDate) as hasCurrentArtifact;
    ');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    $result = $stmt->execute();
    if ($result === false) {
        throw new Exception('Could not check if you have the artifact.');
    }

    return $stmt->fetch(PDO::FETCH_OBJ);
}
