<?php

namespace CharacterGeneratorDev {

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="aioseo-html-sitemap">
	<p>
		</label>
		<input
			type="text"
			class="widefat"
		/>
	</p>
	<p>
			<input
				type="checkbox"
				if ( 'on' === $instance['archives'] ) {
					echo 'checked="checked"';
				}
				?>
				class="widefat"
			/>
		</label>
	</p>
	<p>
			<input
				type="checkbox"
				if ( 'on' === $instance['show_label'] ) {
					echo 'checked="checked"';
				}
				?>
				class="widefat"
			/>
		</label>
	</p>
	<p>
			<input
				type="checkbox"
				if ( 'on' === $instance['publication_date'] ) {
					echo 'checked="checked"';
				}
				?>
				class="widefat"
			/>
		</label>
	</p>

	<p>
		</label>

		<div class="aioseo-columns">
			<div>
				<label>
					<input
						type="checkbox"
					/>
				</label>
			</div>
		</div>
	</p>

	<p>
		</label>

		<div class="aioseo-columns">
			<div>
				<label>
					<input
						type="checkbox"
					/>
				</label>
			</div>
		</div>
	</p>

	<p>
		</label>
			</option>
			</option>
			</option>
			</option>
		</select>
	</p>
	<p>
		</label>
		</select>
	</p>

	<p>
		</label>
		<input
			type="text"
			class="widefat"
		/>
	</p>

	<p>
		</label>
		<input
			type="text"
			class="widefat"
		/>
	</p>
</div>

<style>
	.aioseo-html-sitemap label.aioseo-title,
	.aioseo-html-sitemap label.aioseo-title select {
		color: #141B38 !important;
		font-weight: bold !important;
	}
	.aioseo-html-sitemap .aioseo-description {
		margin-top: -5px;
		font-style: italic;
		font-size: 13px;
	}
	.aioseo-html-sitemap select, .aioseo-html-sitemap input[type=text] {
		margin-top: 8px;
	}
	.aioseo-html-sitemap .aioseo-columns {
		display: flex;
		flex-wrap: wrap;
	}
	.aioseo-html-sitemap .aioseo-columns div {
		flex: 0 0 50%;
	}
</style>
} // namespace CharacterGeneratorDev
