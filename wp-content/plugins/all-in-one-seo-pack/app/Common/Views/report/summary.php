<?php

namespace CharacterGeneratorDev {

/**
 * Summary report view.
 *
 * @since 4.7.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
// phpcs:disable Generic.Files.LineLength.MaxExceeded
?>
<div style="background-color: #f3f4f5; color: #141b38; font-family: Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 22px; margin: 0; padding: 0;">

	<div style="margin: 0 auto; padding: 70px 0; width: 100%; max-width: 680px;">
		<div style="background-color: #ffffff; border: 1px solid #e8e8eb;">
			<div style="padding-left: 20px; padding-right: 20px; padding-bottom: 20px;">
				<table style="table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
					<thead>
					<tr>
						<th style="padding: 0; width: 60%; line-height: 1;"></th>
						<th style="padding: 0; width: 40%; line-height: 1;"></th>
					</tr>
					</thead>

					<tbody>
					<tr>
						<td style="padding: 0;">
							<div style="padding-top: 20px;">
								<img
										style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none;"
										width="100"
										height="20"
										src="https://static.aioseo.io/report/ste/text-logo.jpg"
								/>
							</div>
						</td>

						<td style="padding: 0; word-break: break-word;">
						</td>
					</tr>

					<tr>
						<td style="padding: 0; word-break: break-word;">
							<div style="padding-top: 10px;">
							</div>
						</td>

						<td style="padding: 0; word-break: break-word;">
							<div style="padding-top: 10px; font-size: 12px; text-align: right; line-height: 15px;">
								<a
										style="color: #005ae0; font-weight: normal; text-decoration: none;"
							</div>
						</td>
					</tr>
					</tbody>
				</table>
			</div>

			<div style="background-color: #004f9d; padding-bottom: 20px;">
				<table style="table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
					<thead>
					<tr>
						<th style="padding: 0; width: 70%; line-height: 1;"></th>
						<th style="padding: 0; width: 30%; line-height: 1;"></th>
					</tr>
					</thead>

					<tbody>
					<tr>
						<td style="padding: 0; word-break: break-word;">
							<div style="padding-right: 20px; padding-left: 20px; padding-top: 20px; line-height: 1;">

								<img
										style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle;"
										width="28"
										height="28"
										src="https://static.aioseo.io/report/ste/emoji-1f44b.png"
										alt="Waving Hand Sign"
								/>
							</div>

							<div style="color: #ffffff; padding-right: 20px; padding-left: 20px; padding-top: 20px; font-size: 20px; line-height: 26px;  font-weight: 400;">
							</div>
						</td>

						<td style="padding: 0; text-align: right; word-break: break-word;">
							<img
									style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; padding-top: 20px;"
									width="142"
									height="140"
									alt=""
							/>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>

			<div style="margin-top: 20px;">
				<div style="text-align: center; font-size: 14px; border-radius: 4px; margin: 0; padding: 8px 12px; background-color: #fffbeb; border: 1px solid #f18200;">
					printf(
						// Translators: 1 - The plugin short name ("AIOSEO"), 2 - Opening link tag, 3 - HTML arrow, 4 - Closing link tag.
						__( 'An update is available for %1$s. %2$sUpgrade to the latest version%3$s%4$s', 'all-in-one-seo-pack' ),
						AIOSEO_PLUGIN_SHORT_NAME,
						'<a href="' . ( $links['update'] ?? '#' ) . '" style="color: #005ae0; font-weight: normal; text-decoration: underline;">',
						'&nbsp;&rarr;',
						'</a>'
					)
					?>
				</div>
			</div>

		<div style="background-color: #ffffff; border: 1px solid #e8e8eb; margin-top: 20px;">
			<div style="border-bottom: 1px solid #e5e5e5; padding: 15px 20px;">
				<img
						style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; margin-right: 6px;"
						width="35"
						height="35"
						src="https://static.aioseo.io/report/ste/icon-report.png"
						alt=""
				/>

			</div>

			<div style="padding: 20px;">
					<table style="border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
						<tbody>
						<tr>
							<td style="padding: 0; word-break: break-word;">
							</td>

							<td style="text-align: right; word-break: break-word;">
									<a
											style="color: #005ae0; font-weight: 700; text-decoration: underline;"
									>
									</a>
							</td>
						</tr>
						</tbody>
					</table>

					<div style="margin-top: 16px; overflow-x: auto;">
						<table style="min-width: 460px; table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
							<thead>
							<tr style="border-color: #ffffff; border-bottom-width: 6px; border-bottom-style: solid;">
								<th style="width: 59%; background-color: #f0f6ff; padding: 12px; font-size: 12px; font-weight: 400; color: #434960; border-top-left-radius: 2px; border-bottom-left-radius: 2px; line-height: 1;">
								</th>

										TruSEO
								</th>

								<th style="width: 12%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; color: #434960;  line-height: 1;">
								</th>

								<th style="width: 12%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; color: #434960; border-top-right-radius: 2px; border-bottom-right-radius: 2px; line-height: 1;">
								</th>
							</tr>
							</thead>

							<tbody>
									<td style="padding: 0; word-break: break-word;">
										<div style="padding: 6px; font-size: 14px;">
											</a>
										</div>
									</td>

									<td style="padding: 0; word-break: break-word;">
											<div style="padding: 6px;">
												</div>
											</div>
									</td>

									<td style="padding: 0; word-break: break-word;">
										<div style="padding: 6px; font-size: 14px;">
										</div>
									</td>

									<td style="padding: 0; word-break: break-word;">
												<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-bottom-color: #00aa63; border-bottom-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-top-width: 0;"></div>

												<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-top-color: #df2a4a; border-top-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-bottom-width: 0;"></div>

										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>

						<div style="margin-top: 20px; margin-bottom: 20px; border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>

					<table style="border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
						<tbody>
						<tr>
							<td style="padding: 0; word-break: break-word;">
							</td>

							<td style="text-align: right; word-break: break-word;">
									<a
											style="color: #005ae0; font-weight: 700; text-decoration: underline;"
									>
									</a>
							</td>
						</tr>
						</tbody>
					</table>

					<div style="margin-top: 16px; overflow-x: auto;">
						<table style="table-layout: fixed; min-width: 460px; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
							<thead>
							<tr style="border-color: #ffffff; border-bottom-width: 6px; border-bottom-style: solid;">
								<th style="width: 59%; background-color: #f0f6ff; padding: 12px; font-size: 12px; font-weight: 400; color: #434960; border-top-left-radius: 2px; border-bottom-left-radius: 2px; line-height: 1;">
								</th>

										TruSEO
								</th>

								<th style="width: 12%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; color: #434960; line-height: 1;">
								</th>

								<th style="width: 12%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; color: #434960; border-top-right-radius: 2px; border-bottom-right-radius: 2px; line-height: 1;">
								</th>
							</tr>
							</thead>

							<tbody>
									<td style="padding: 0; word-break: break-word;">
										<div style="padding: 6px; font-size: 14px;">
											</a>
										</div>
									</td>

									<td style="padding: 0; word-break: break-word;">
											<div style="padding: 6px;">
												</div>
											</div>
									</td>

									<td style="padding: 0; word-break: break-word;">
										<div style="padding: 6px; font-size: 14px;">
										</div>
									</td>

									<td style="padding: 0; word-break: break-word;">
												<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-bottom-color: #00aa63; border-bottom-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-top-width: 0;"></div>

												<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-top-color: #df2a4a; border-top-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-bottom-width: 0;"></div>

										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>

						<div style="margin-top: 20px; margin-bottom: 20px; border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>
					<div style="overflow-x: auto;">
						<table style="min-width: 600px; table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
							<thead>
							<tr>
								<th style="width: 47%; line-height: 1;"></th>
								<th style="width: 6%; line-height: 1;"></th>
								<th style="width: 47%; line-height: 1;"></th>
							</tr>
							</thead>

							<tbody>
							<tr style="height: 1px;">
								<td style="vertical-align: top; word-break: break-word;">
									<table style="border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
										<tbody>
										<tr>
											<td style="padding: 0; word-break: break-word;">
												<p style="font-size: 16px; margin-bottom: 0; margin-top: 0; font-weight: 700;">
												</p>
											</td>

											<td style="text-align: right; word-break: break-word;">
													<a
															style="color: #005ae0; font-weight: 700; text-decoration: underline;"
													>
													</a>
											</td>
										</tr>
										</tbody>
									</table>

									<table style="margin-top: 16px; table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
										<thead>
										<tr style="border-color: #ffffff; border-bottom-width: 6px; border-bottom-style: solid;">
											<th style="width: 64%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; border-top-left-radius: 2px; border-bottom-left-radius: 2px; line-height: 1;">
											</th>
											<th style="width: 16%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; color: #434960; line-height: 1;">
											</th>
											<th style="width: 20%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; color: #434960; border-top-right-radius: 2px; border-bottom-right-radius: 2px; line-height: 1;">
											</th>
										</tr>
										</thead>

										<tbody>
												<td style="padding: 0; word-break: break-word;">
													<div style="padding: 6px; font-size: 14px;">
													</div>
												</td>

												<td style="padding: 0; word-break: break-word;">
													<div style="padding: 6px; font-size: 14px;">
													</div>
												</td>

												<td style="padding: 0; word-break: break-word;">
															<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-bottom-color: #00aa63; border-bottom-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-top-width: 0;"></div>

															<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-top-color: #df2a4a; border-top-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-bottom-width: 0;"></div>

													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</td>

								<td style="height: inherit; padding: 0; text-align: center; vertical-align: baseline; overflow: hidden; word-break: break-word;">
									<div style="width: 1px; margin-left: auto; margin-right: auto; background-color: #e5e5e5; height: 100%;"></div>
								</td>

								<td style="vertical-align: top; word-break: break-word;">
									<table style="border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
										<tbody>
										<tr>
											<td style="padding: 0; word-break: break-word;">
												<p style="font-size: 16px; margin-bottom: 0; margin-top: 0; font-weight: 700;">
												</p>
											</td>

											<td style="text-align: right; word-break: break-word;">
													<a
															style="color: #005ae0; font-weight: 700; text-decoration: underline;"
													>
													</a>
											</td>
										</tr>
										</tbody>
									</table>

									<table style="margin-top: 16px; table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
										<thead>
										<tr style="border-color: #ffffff; border-bottom-width: 6px; border-bottom-style: solid;">
											<th style="width: 64%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; border-top-left-radius: 2px; border-bottom-left-radius: 2px; line-height: 1;">
											</th>
											<th style="width: 16%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; color: #434960; line-height: 1;">
											</th>
											<th style="width: 20%; background-color: #f0f6ff; padding: 6px; font-size: 12px; font-weight: 400; color: #434960; border-top-right-radius: 2px; border-bottom-right-radius: 2px; line-height: 1;">
											</th>
										</tr>
										</thead>

										<tbody>
												<td style="padding: 0; word-break: break-word;">
													<div style="padding: 6px; font-size: 14px;">
													</div>
												</td>

												<td style="padding: 0; word-break: break-word;">
													<div style="padding: 6px; font-size: 14px;">
													</div>
												</td>

												<td style="padding: 0; word-break: break-word;">
															<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-bottom-color: #00aa63; border-bottom-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-top-width: 0;"></div>

															<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-top-color: #df2a4a; border-top-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-bottom-width: 0;"></div>

													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							</tbody>
						</table>
					</div>

					<div>
						<img
								style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle;"
								width="638"
								height="146"
								src="https://static.aioseo.io/report/ste/banner-search-statistics-cta-upsell.jpg"
								alt=""
						/>

						<p style="font-size: 16px; margin-bottom: 0; margin-top: 20px; text-align: center;">
						</p>

						<div style="width: 475px; max-width: 96%; margin-top: 20px; margin-left: auto; margin-right: auto;">
							<div style="width: 210px; padding: 6px; display: inline-block; vertical-align: middle;">
								<img
										style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; margin-right: 3px;"
										width="17"
										height="17"
										src="https://static.aioseo.io/report/ste/icon-check-circle-out.png"
										alt="&#10003;"
								/>

							</div>

							<div style="width: 210px; padding: 6px; display: inline-block; vertical-align: middle;">
								<img
										style="margin-right: 3px; border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle;"
										width="17"
										height="17"
										src="https://static.aioseo.io/report/ste/icon-check-circle-out.png"
										alt="&#10003;"
								/>

							</div>

							<div style="width: 210px; padding: 6px; display: inline-block; vertical-align: middle;">
								<img
										style="margin-right: 3px; border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle;"
										width="17"
										height="17"
										src="https://static.aioseo.io/report/ste/icon-check-circle-out.png"
										alt="&#10003;"
								/>

							</div>

							<div style="width: 210px; padding: 6px; display: inline-block; vertical-align: middle;">
								<img
										style="margin-right: 3px; border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle;"
										width="17"
										height="17"
										src="https://static.aioseo.io/report/ste/icon-check-circle-out.png"
										alt="&#10003;"
								/>

							</div>
						</div>

						<div style="margin-top: 20px; text-align: center;">
							<a
									style="border-radius: 4px; border: none; display: inline-block; font-size: 14px; font-style: normal; font-weight: 700; text-align: center; text-decoration: none; user-select: none; vertical-align: middle; background-color: #00aa63; color: #ffffff; padding: 8px 20px;"
							>
							</a>
						</div>
					</div>

					<div style="margin-top: 20px; margin-bottom: 20px; border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>

					<div style="text-align: center;">
						<a
								style="border-radius: 4px; border: none; display: inline-block; font-size: 14px; font-style: normal; font-weight: 700; text-align: center; text-decoration: none; user-select: none; vertical-align: middle; background-color: #005ae0; color: #ffffff; padding: 8px 20px;"
						>
						</a>
					</div>
			</div>
		</div>

			<div style="background-color: #ffffff; border: 1px solid #e8e8eb; margin-top: 20px;">
				<div style="border-bottom: 1px solid #e5e5e5; padding: 15px 20px;">
					<img
							style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; margin-right: 6px;"
							width="35"
							height="35"
							src="https://static.aioseo.io/report/ste/icon-summary.png"
							alt=""
					/>

				</div>

				<div style="padding: 20px;">
						<div>
							<table style="border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
								<tbody>
								<tr>
									<td style="padding: 0; word-break: break-word;">
										<p style="font-size: 16px; margin-bottom: 0; margin-top: 0; font-weight: 700;">
										</p>
									</td>

									<td style="text-align: right; word-break: break-word;">
										<a
												style="color: #005ae0; font-weight: 700; text-decoration: underline;"
										>
										</a>
									</td>
								</tr>
								</tbody>
							</table>

							<div style="margin-top: 16px; overflow-x: auto;">
								<table style="table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
									<thead>
									<tr>
										<th style="background-color: #f0f6ff; padding: 12px; font-size: 12px; font-weight: 400; color: #434960; border-top-left-radius: 2px; border-bottom-left-radius: 2px; width: 210px; line-height: 1;">
										</th>

												TruSEO
										</th>

										</th>
									</tr>
									</thead>

									<tbody>
											<tr>
												<td
														colspan="3"
														style="padding: 0; word-break: break-word;"
												>
													<div style="border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>
												</td>
											</tr>
										<tr>
											<td style="padding: 12px; word-break: break-word;">
												<table style="table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
													<thead>
													<tr>
														<th style="width: 35%; padding: 0; line-height: 1;"></th>
														<th style="width: 65%; padding: 0; line-height: 1;"></th>
													</tr>
													</thead>

													<tbody>
													<tr>
														<td style="padding: 0; word-break: break-word;">
															<div style="width: 100%; height: 65px; position: relative; overflow: hidden;">
																<a
																		style="color: #005ae0; font-weight: normal; text-decoration: underline;"
																>
																	<img
																			style="box-sizing: border-box; display: inline-block; font-size: 14px; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; position: absolute; width: 100%; height: 100%; top: 0; right: 0; bottom: 0; left: 0; object-position: center; object-fit: cover; border-width: 1px; border-style: solid; border-color: #e5e5e5;"
																	/>
																</a>
															</div>
														</td>

														<td style="padding: 0; word-break: break-word;">
															<div style="padding: 6px; display: inline-block; vertical-align: middle; font-size: 14px;">
																<a
																		style="color: #141b38; font-weight: normal; text-decoration: none;"
																>
																</a>
															</div>
														</td>
													</tr>
													</tbody>
												</table>
											</td>

											<td style="padding: 0; word-break: break-word;">
													<div style="padding: 12px;">
														</div>
													</div>
											</td>

											<td style="padding: 0; word-break: break-word;">
													<div style="padding: 12px; font-size: 14px;">
																<img
																		style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle;"
																		width="19"
																		height="19"
																		alt=""
																/>


															</div>
													</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>

							<div style="margin-top: 30px; margin-bottom: 30px; border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>
						<div>
							<table style="border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
								<tbody>
								<tr>
									<td style="padding: 0; word-break: break-word;">
										<p style="font-size: 16px; margin-bottom: 0; margin-top: 0; font-weight: 700;">
										</p>
									</td>

									<td style="text-align: right; word-break: break-word;">
										<a
												style="color: #005ae0; font-weight: 700; text-decoration: underline;"
										>
										</a>
									</td>
								</tr>
								</tbody>
							</table>

							<div style="margin-top: 16px; overflow-x: auto;">
								<table style="table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
									<thead>
									<tr>
										<th style="width: 319px; background-color: #f0f6ff; padding: 0; font-size: 12px; font-weight: 400; color: #434960; border-top-left-radius: 2px; border-bottom-left-radius: 2px; line-height: 1;">
										</th>

												<div style="padding: 12px;">TruSEO</div>
										</th>

										<th style="width: 159px; text-align: center; background-color: #f0f6ff; padding: 0; font-size: 12px; font-weight: 400; color: #434960; border-top-right-radius: 2px; border-bottom-right-radius: 2px; line-height: 1;">
										</th>
									</tr>
									</thead>

									<tbody>
										<tr>
											<td
													colspan="3"
													style="padding-bottom: 8px; padding-top: 8px; word-break: break-word;"
											></td>
										</tr>

										<tr style="border-width: 1px; border-style: solid; border-color: #e5e5e5;">
											<td
													colspan="3"
													style="padding: 12px; word-break: break-word;"
											>
												<table style="table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
													<thead>
													<tr>
														<th style="width: 311px; padding: 0; line-height: 1;"></th>
														<th style="width: 151px; padding: 0; line-height: 1;"></th>
													</tr>
													</thead>

													<tbody>
													<tr>
														<td style="padding: 0; background-color: #f3f4f5; word-break: break-word;">
															<table style="table-layout: fixed; border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
																<thead>
																<tr>
																	<th style="width: 35%; padding: 0; line-height: 1;"></th>
																	<th style="width: 65%; padding: 0; line-height: 1;"></th>
																</tr>
																</thead>

																<tbody>
																<tr>
																	<td style="padding: 0; word-break: break-word;">
																		<div style="width: 100%; height: 65px; position: relative; overflow: hidden;">
																				<img
																						style="box-sizing: border-box; display: inline-block; font-size: 14px; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; position: absolute; width: 100%; height: 100%; top: 0; right: 0; bottom: 0; left: 0; object-position: center; object-fit: cover; border-width: 1px; border-style: solid; border-color: #e5e5e5;"
																				/>
																			</a>
																		</div>
																	</td>

																	<td style="padding: 0; word-break: break-word;">
																		<div style="padding: 6px; display: inline-block; vertical-align: middle; font-size: 14px;">
																			</a>
																		</div>
																	</td>
																</tr>
																</tbody>
															</table>
														</td>

														<td style="padding: 0; background-color: #f3f4f5; word-break: break-word;">
																</div>
														</td>

														<td style="padding: 0; background-color: #f3f4f5; word-break: break-word;">
																	<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-bottom-color: #00aa63; border-bottom-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-top-width: 0;"></div>

																	<div style="display: inline-block; vertical-align: middle; margin-right: 3px; width: 0; border-style: solid; border-top-color: #df2a4a; border-top-width: 5px; border-left-color: transparent; border-right-color: transparent; border-left-width: 5px; border-right-width: 5px; border-bottom-width: 0;"></div>

															</div>
														</td>
													</tr>

														<tr>
															<td
																	colspan="2"
																	style="padding: 10px 0 0; word-break: break-word;"
															>
															</td>

															<td style="padding: 10px 0 0; text-align: right; word-break: break-word;">
																	<a
																			style="color: #005ae0; font-weight: normal; text-decoration: underline; font-size: 12px;"
																	>
																	</a>
															</td>
														</tr>

														<tr>
															<td
																	colspan="3"
																	style="padding: 0; word-break: break-word;"
															>
																	<div style="padding-top: 0; padding-left: 0; padding-right: 0; pt-6 font-size: 14px;">
																		<img
																				style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; margin-right: 3px;"
																				width="15"
																				height="15"
																				src="https://static.aioseo.io/report/ste/icon-remove.png"
																				alt="x"
																		/>

																	</div>
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>

						<div style="font-size: 16px; font-weight: 400; text-align: center;">
						</div>

					<div style="margin-top: 20px; text-align: center;">
						<a
								style="border-radius: 4px; border: none; display: inline-block; font-size: 14px; font-style: normal; font-weight: 700; text-align: center; text-decoration: none; user-select: none; vertical-align: middle; background-color: #005ae0; color: #ffffff; padding: 8px 20px;"
						>
						</a>
					</div>
				</div>
			</div>

			<div style="background-color: #ffffff; border: 1px solid #e8e8eb; margin-top: 20px;">
				<div style="border-bottom: 1px solid #e5e5e5; padding: 15px 20px;">
					<img
							style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; margin-right: 6px;"
							width="35"
							height="35"
							src="https://static.aioseo.io/report/ste/icon-flag.png"
							alt=""
					/>

				</div>

				<div style="padding: 10px; overflow-x: auto;">
					<table style="min-width: 400px; width: 100%;">
						<tbody>
							<tr>
										<img
												style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; margin-left: auto; margin-right: auto;"
												width="35"
												height="35"
												alt=""
										/>

										<p style="font-size: 16px; margin-bottom: 0; margin-top: 12px;">
										</p>
									</td>
							</tr>
						</tbody>
					</table>

						<div style="margin: 10px 10px 20px; border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>

						<div style="padding-bottom: 10px; text-align: center;">
							<a
									style="border-radius: 4px; border: none; display: inline-block; font-size: 14px; font-style: normal; font-weight: 700; text-align: center; text-decoration: none; user-select: none; vertical-align: middle; background-color: #005ae0; color: #ffffff; padding: 8px 20px;"
							>
							</a>
						</div>
				</div>
			</div>

			<div style="background-color: #ffffff; border: 1px solid #e8e8eb; margin-top: 20px;">
				<div style="border-bottom: 1px solid #e5e5e5; padding: 15px 20px;">
					<img
							style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; margin-right: 6px;"
							width="35"
							height="35"
							src="https://static.aioseo.io/report/ste/icon-star.png"
							alt=""
					/>

				</div>

				<div style="padding: 20px;">
					<table style="border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
						<tbody>
								<tr>
									<td
											colspan="3"
											style="padding-bottom: 8px; padding-top: 8px; word-break: break-word;"
									>
										<div style="border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>
									</td>
								</tr>

							<tr>
								<td style="padding: 0; word-break: break-word;">
									<div style="width: 147px; height: 82px; margin-top: 6px; margin-bottom: 6px; margin-right: 6px; display: inline-block; vertical-align: top; position: relative; overflow: hidden;">
										<a
												style="color: #005ae0; font-weight: normal; text-decoration: underline;"
										>
											<img
													style="box-sizing: border-box; display: inline-block; font-size: 14px; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; position: absolute; width: 100%; height: 100%; top: 0; right: 0; bottom: 0; left: 0; object-position: center; object-fit: cover; border-width: 1px; border-style: solid; border-color: #e5e5e5;"
											/>
										</a>
									</div>

									<div style="max-width: 448px; padding-bottom: 3px; padding-top: 3px; display: inline-block; vertical-align: top;">
										<a
												style="color: #141b38; font-weight: 700; text-decoration: none; font-size: 16px;"
										>
										</a>

										<div style="margin-top: 6px; font-size: 14px;">

											<a
													style="color: #005ae0; font-weight: normal; text-decoration: underline;"
											>
											</a>
										</div>
									</div>
								</td>
							</tr>
						</tbody>
					</table>

					<div style="padding-top: 8px;">
						<div style="border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>
					</div>

					<div style="padding-top: 20px; text-align: center;">
						<a
								style="border-radius: 4px; border: none; display: inline-block; font-size: 14px; font-style: normal; font-weight: 700; text-align: center; text-decoration: none; user-select: none; vertical-align: middle; background-color: #005ae0; color: #ffffff; padding: 8px 20px;"
						>
						</a>
					</div>
				</div>
			</div>

		<div style="width: 600px; max-width: 90%; margin-top: 20px; margin-left: auto; margin-right: auto;">
			<div style="text-align: center;">
				<img
						style="border-radius: 9999px; border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; margin-left: auto; margin-right: auto;"
						src="https://static.aioseo.io/report/ste/danny-circle.jpg"
						width="50"
						height="50"
				/>

				<p style="font-size: 14px; margin-bottom: 0; margin-top: 20px; text-align: center; color: #434960;">
					// Translators: 1 - The plugin short name ("AIOSEO").
					printf( esc_html__( 'This email was auto-generated and sent from %1$s.', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_SHORT_NAME )
					?>
				</p>

				<p style="font-size: 14px; margin-bottom: 0; margin-top: 0; text-align: center; color: #434960;">
					printf(
						// Translators: 1 - Opening link tag, 2 - Closing link tag.
						esc_html__( 'Learn how to %1$sdisable%2$s it.', 'all-in-one-seo-pack' ),
						'<a href="' . ( $links['disable'] ?? '#' ) . '" style="color: #141b38; font-weight: normal; text-decoration: underline;">', '</a>'
					)
					?>
				</p>
			</div>

			<div style="margin-top: 20px;">
				<div style="border-top-width: 0; border-bottom-width: 1px; border-style: solid; border-color: #e5e5e5;"></div>
			</div>

			<div style="margin-top: 20px;">
				<table style="border-collapse: collapse; text-align: left; vertical-align: middle; width: 100%;">
					<tbody>
					<tr>
						<td style="line-height: 1; word-break: break-word;">
							<a
									style="color: #005ae0; font-weight: normal; text-decoration: none; display: inline-block;"
							>
								<img
										style="border: none; box-sizing: border-box; display: inline-block; font-size: 14px; height: auto; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle;"
										width="82"
										height="17"
										src="https://static.aioseo.io/report/ste/text-logo.png"
								/>
							</a>
						</td>

						<td style="line-height: 1; text-align: right; word-break: break-word;">
							<a
									style="margin-right: 6px; color: #005ae0; font-weight: normal; text-decoration: none; display: inline-block;"
							>
								<img
										style="border-radius: 2px; border: none; box-sizing: border-box; display: inline-block; font-size: 14px; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; height: 20px; width: 20px;"
										src="https://static.aioseo.io/report/ste/facebook.jpg"
										alt="Fb"
										width="20"
										height="20"
								/>
							</a>

							<a
									style="margin-right: 6px; color: #005ae0; font-weight: normal; text-decoration: none; display: inline-block;"
							>
								<img
										style="border-radius: 2px; border: none; box-sizing: border-box; display: inline-block; font-size: 14px; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; height: 20px; width: 20px;"
										src="https://static.aioseo.io/report/ste/linkedin.jpg"
										alt="In"
										width="20"
										height="20"
								/>
							</a>

							<a
									style="margin-right: 6px; color: #005ae0; font-weight: normal; text-decoration: none; display: inline-block;"
							>
								<img
										style="border-radius: 2px; border: none; box-sizing: border-box; display: inline-block; font-size: 14px; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; height: 20px; width: 20px;"
										src="https://static.aioseo.io/report/ste/youtube.jpg"
										alt="Yt"
										width="20"
										height="20"
								/>
							</a>

							<a
									style="color: #005ae0; font-weight: normal; text-decoration: none; display: inline-block;"
							>
								<img
										style="border-radius: 2px; border: none; box-sizing: border-box; display: inline-block; font-size: 14px; line-height: 1; max-width: 100%; text-decoration: none; vertical-align: middle; height: 20px; width: 20px;"
										src="https://static.aioseo.io/report/ste/x.jpg"
										alt="Tw"
										width="20"
										height="20"
								/>
							</a>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
} // namespace CharacterGeneratorDev
