<?php

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package MadeIndonesia
 */

defined('ABSPATH') || die("Can't access directly");
?>

<footer id="colophon" class="site-footer">
	<div class="site-info">
		<a href="<?php echo esc_url(__('https://wordpress.org/', 'themedomain')); ?>"><?php
																						/* translators: %s: CMS name, i.e. WordPress. */
																						printf(esc_html__('Proudly powered by %s', 'themedomain'), 'WordPress');
																						?></a>
		<span class="sep"> | </span>
		<?php
		/* translators: 1: Theme name, 2: Theme author. */
		printf(esc_html__('Theme: %1$s by %2$s.', 'themedomain'), 'themedomain', '<a href="https://madeindonesia.com/">MadeIndonesia</a>');
		?>
	</div><!-- .site-info -->
</footer><!-- #colophon -->
</div>
<!-- /#page: the opening tag is in header.php -->

<?php wp_footer(); ?>

</body>

</html>