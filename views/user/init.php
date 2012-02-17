<?php
switch ($action) {
    case 'start':
        ?>
<h1>Account initialization</h1>
<div>
    Please fill in next few forms in order to initialize your account.
    <br /> <a href="<?php echo $this->createUrl('', array('action'=>'next')); ?>">Start</a>
</div>
        <?php
        break;
    case 'finish':
        ?>

        <h1>Account initialization</h1>
        <div>
            Account was successfully initialized.
            <br /> <a href="<?php echo $this->createUrl('', array('action'=>'next')); ?>">Finish</a>
        </div>
        <?php
        break;
}