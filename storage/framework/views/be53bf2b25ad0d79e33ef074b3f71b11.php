<?php $__env->startSection('title', 'Login'); ?>

<?php $__env->startSection('card-title', 'Welcome Back'); ?>

<?php $__env->startSection('content'); ?>

    <form method="POST" action="/login">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input 
                type="email" 
                id="email"
                name="email" 
                value="<?php echo e(old('email')); ?>" 
                placeholder="you@example.com"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input 
                type="password" 
                id="password"
                name="password" 
                placeholder="••••••••"
                required
            >
        </div>

        <div class="form-group" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="remember" id="remember" style="width:auto;">
            <label for="remember" style="margin:0;">Remember me</label>
        </div>

        <button type="submit" class="btn">Login</button>
    </form>

    <p class="link-text">
        Don't have an account? <a href="/register">Register here</a>
    </p>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\brend\.vscode\SOFTWARE-DISCUSSION-FORUM\resources\views/login.blade.php ENDPATH**/ ?>