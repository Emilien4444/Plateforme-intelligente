<?php include '../Principale/header.php'; ?>
<div class="form-container">
    <h2>Contactez-nous</h2>
    <form method="POST">
        <input type="text" name="name" placeholder="Nom" required>
        <input type="email" name="email" placeholder="Email" required>
        <textarea name="message" placeholder="Votre message" required></textarea>
        <button type="submit">Envoyer</button>
    </form>
</div>
<?php include '../Principale/footer.php'; ?>
