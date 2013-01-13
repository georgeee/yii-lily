<h1>Delete an account</h1>
<div>
    <form action="" method='post'  >
        <p class="note">Do you really want to delete your account <?php echo $account->displayId; ?> (service <?php echo $account->serviceName; ?>)?</p>
        <br>
        <input type="submit" name="accept" value="Yes"/>
        <?php echo CHtml::link('Cancel', $this->createUrl('account/list')); ?>
    </form>
</div>