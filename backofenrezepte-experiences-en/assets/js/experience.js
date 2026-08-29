(function () {

	"use strict";


	function initExperienceCard() {

		const form =
			document.getElementById(
				"br-experience-form"
			);


		if (!form) {
			return;
		}


		/*
		 * Prevent duplicate initialization.
		 */
		if (
			form.dataset.brExperienceReady === "1"
		) {
			return;
		}


		form.dataset.brExperienceReady = "1";


		/* =====================================================
		   ELEMENTS
		   ===================================================== */

		const recipeIdField =
			document.getElementById(
				"br-experience-recipe-id"
			);

		const timestampField =
			document.getElementById(
				"br-experience-ts"
			);

		const honeypotField =
			document.getElementById(
				"br-experience-hp-field"
			);

		const problemSection =
			document.getElementById(
				"br-problem-section"
			);

		const errorBox =
			document.getElementById(
				"br-experience-error"
			);

		const successBox =
			document.getElementById(
				"br-experience-success"
			);

		const experienceId =
			document.getElementById(
				"br-experience-id"
			);

		const submitButton =
			document.getElementById(
				"br-experience-submit"
			);

		const submitText =
			submitButton
				? submitButton.querySelector(
					".br-submit-text"
				)
				: null;

		const submitLoading =
			submitButton
				? submitButton.querySelector(
					".br-submit-loading"
				)
				: null;

		const changesField =
			document.getElementById(
				"br-changes"
			);

		const commentField =
			document.getElementById(
				"br-comment"
			);

		const changesCount =
			document.getElementById(
				"br-changes-count"
			);

		const commentCount =
			document.getElementById(
				"br-comment-count"
			);


		/* =====================================================
		   SAFETY CHECK
		   ===================================================== */

		if (
			!recipeIdField ||
			!timestampField ||
			!honeypotField ||
			!problemSection ||
			!errorBox ||
			!successBox ||
			!experienceId ||
			!submitButton
		) {
			return;
		}


		/* =====================================================
		   CONFIGURATION FROM WORDPRESS
		   ===================================================== */

		const config =
			window.BackofenRezepteExperience || {};


		let recipeId =
			config.recipeId ||
			config.recipe_id ||
			null;


		/*
		 * Fallback to WordPress body class.
		 */
		if (!recipeId) {

			const bodyClasses =
				document.body.className || "";


			const match =
				bodyClasses.match(
					/(?:^|\s)postid-(\d+)(?:\s|$)/
				);


			if (match) {

				recipeId =
					parseInt(
						match[1],
						10
					);

			}

		}


		if (recipeId) {

			recipeIdField.value =
				String(recipeId);

		}


		/*
		 * REST endpoint.
		 */
		const restUrl =
			config.restUrl ||
			(
				window.location.origin +
				"/wp-json/backofenrezepte/v1/experiences"
			);


		/*
		 * Nonce is optional for this public endpoint.
		 */
		const nonce =
			config.nonce || "";


		/*
		 * Timestamp.
		 */
		timestampField.value =
			String(
				Math.floor(
					Date.now() / 1000
				)
			);


		/* =====================================================
		   RESULT → PROBLEM
		   ===================================================== */

		const resultInputs =
			form.querySelectorAll(
				'input[name="result"]'
			);


		resultInputs.forEach(
			function (input) {

				input.addEventListener(
					"change",
					function () {

						const showProblem =
							input.value !== "gelungen";


						problemSection.hidden =
							!showProblem;


						if (!showProblem) {

							form
								.querySelectorAll(
									'input[name="problem"]'
								)
								.forEach(
									function (problem) {

										problem.checked =
											false;

									}
								);

						}

					}
				);

			}
		);


		/* =====================================================
		   CHARACTER COUNTERS
		   ===================================================== */

		function updateCounter(
			field,
			counter
		) {

			if (!field || !counter) {
				return;
			}


			counter.textContent =
				String(
					field.value.length
				);

		}


		if (changesField) {

			changesField.addEventListener(
				"input",
				function () {

					updateCounter(
						changesField,
						changesCount
					);

				}
			);

		}


		if (commentField) {

			commentField.addEventListener(
				"input",
				function () {

					updateCounter(
						commentField,
						commentCount
					);

				}
			);

		}


		/* =====================================================
		   ERROR
		   ===================================================== */

		function showError() {

			errorBox.hidden =
				false;

		}


		/* =====================================================
		   SUBMIT STATE
		   ===================================================== */

		function setSubmitting(
			submitting
		) {

			submitButton.disabled =
				submitting;


			if (submitText) {

				submitText.hidden =
					submitting;

			}


			if (submitLoading) {

				submitLoading.hidden =
					!submitting;

			}

		}


		/* =====================================================
		   SUBMIT
		   ===================================================== */

		form.addEventListener(
			"submit",
			async function (event) {

				/*
				 * THIS IS THE FIRST ACTION.
				 *
				 * Even if the API later fails,
				 * the browser must not perform
				 * the normal form submission.
				 */
				event.preventDefault();


				errorBox.hidden =
					true;


				/* ---------------------------------------------
				   Recipe ID
				   --------------------------------------------- */

				const currentRecipeId =
					parseInt(
						recipeIdField.value,
						10
					);


				if (
					!currentRecipeId ||
					currentRecipeId < 1
				) {

					showError();

					return;

				}


				/* ---------------------------------------------
				   Browser validation
				   --------------------------------------------- */

				if (
					!form.checkValidity()
				) {

					form.reportValidity();

					return;

				}


				/* ---------------------------------------------
				   Honeypot
				   --------------------------------------------- */

				if (
					honeypotField.value.trim() !== ""
				) {

					showError();

					return;

				}


				/* ---------------------------------------------
				   FormData
				   --------------------------------------------- */

				const formData =
					new FormData(form);


				/* ---------------------------------------------
				   Base payload
				   --------------------------------------------- */

				const payload = {

					recipe_id:
						currentRecipeId,

					oven_type:
						String(
							formData.get(
								"oven_type"
							) || ""
						),

					result:
						String(
							formData.get(
								"result"
							) || ""
						),

					problem:
						String(
							formData.get(
								"problem"
							) || ""
						),

					form:
						String(
							formData.get(
								"form"
							) || ""
						),

					quantity:
						String(
							formData.get(
								"quantity"
							) || ""
						),

					changes:
						String(
							formData.get(
								"changes"
							) || ""
						),

					comment:
						String(
							formData.get(
								"comment"
							) || ""
						),

					hp_field:
						String(
							formData.get(
								"hp_field"
							) || ""
						),

					ts:
						Number(
							formData.get(
								"ts"
							)
						)

				};


				/* ---------------------------------------------
				   Optional temperature
				   --------------------------------------------- */

				const temperatureValue =
					formData.get(
						"temperature"
					);


				if (
					temperatureValue !== null &&
					String(
						temperatureValue
					).trim() !== ""
				) {

					payload.temperature =
						Number(
							temperatureValue
						);

				}


				/* ---------------------------------------------
				   Optional time
				   --------------------------------------------- */

				const timeValue =
					formData.get(
						"time_minutes"
					);


				if (
					timeValue !== null &&
					String(
						timeValue
					).trim() !== ""
				) {

					payload.time_minutes =
						Number(
							timeValue
						);

				}


				/* ---------------------------------------------
				   Loading
				   --------------------------------------------- */

				setSubmitting(true);


				try {

					const headers = {

						"Content-Type":
							"application/json",

						"Accept":
							"application/json"

					};


					if (nonce) {

						headers["X-WP-Nonce"] =
							nonce;

					}


					/* -----------------------------------------
					   POST
					   ----------------------------------------- */

					const response =
						await fetch(
							restUrl,
							{
								method: "POST",

								headers: headers,

								credentials:
									"same-origin",

								body:
									JSON.stringify(
										payload
									)
							}
						);


					/* -----------------------------------------
					   Response
					   ----------------------------------------- */

					let data = null;


					try {

						data =
							await response.json();

					} catch (
						jsonError
					) {

						data = null;

					}


					/* -----------------------------------------
					   SUCCESS
					   ----------------------------------------- */

					if (
						response.ok &&
						data &&
						data.success === true
					) {

						if (
							data.experience_id
						) {

							experienceId.textContent =
								String(
									data.experience_id
								);

						}


						form.hidden =
							true;


						successBox.hidden =
							false;


						successBox.scrollIntoView({
							behavior: "smooth",
							block: "center"
						});


						return;

					}


					/* -----------------------------------------
					   API ERROR
					   ----------------------------------------- */

					showError();


				} catch (
					networkError
				) {

					showError();

				} finally {

					setSubmitting(false);

				}

			}
		);

	}


	/* =======================================================
	   START
	   ======================================================= */

	if (
		document.readyState === "loading"
	) {

		document.addEventListener(
			"DOMContentLoaded",
			initExperienceCard
		);

	} else {

		initExperienceCard();

	}

})();
