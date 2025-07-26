<?php

namespace CharacterGeneratorDev {

/*
Template Name: Updates Page
*/

get_header();
?>
<main>
  <section class="update-header">
    <h2>Character Builder Development Update</h2>
  </section>

  <section class="update-log">
    <h3>🛠️ Update 16/07/2025</h3>
    <p>After the last feature implementation borked the character builder, here's the recovery plan for the next week:</p>
    <ol>
      <li><s>Restore <strong>New Character</strong> / <strong>Load Character</strong> splash screen</s></li>
      <li><s>Fix bug: <strong>Species</strong> and <strong>Career</strong> dropdowns fail to reload after reopening</s></li>
      <li><s>Restore <strong>Gift Selection</strong> dropdowns for free gifts</s></li>
      <li><s>Restore Gift reqiures gift functionality</s></li>
      <li><s>Restore <strong>Increased Trait</strong> functionality</s></li>
      <li><s>Restore <strong>Skill Display</strong> functions</s></li>
      <li><s>Restore <strong>Dice Pool Display</strong></s></li>
      <li><s>Restore <strong>Summary Page</strong></s></li>
      <li>Restore ability to save and load characters</li>
      <li>Enable <strong>Save to PDF</strong> function</li>
      <li>Enable <strong>Development / Production</strong> environments to ensure builder availability</li>
    </ol>

    <p><em>Once stability is restored, we’ll begin rolling out new features—without jeopardizing availability:</em></p>
    <ol start="10">
      <li>Make UI enhancements</li>
      <li>Enable <strong>Language Dropdown</strong> function</li>
      <li>Enable <strong>Language Requirements</strong> on gift selection</li>
      <li>Enable <strong>Trait Requirements</strong> on gift selection</li>
      <li>Enable <strong>Literacy Requirements</strong> on gift selection</li>
      <li>Enable <strong>Literacy Dropdown</strong> function</li>
      <li>Enable <strong>Extra Career Functions</strong> on gift selecton</li>
      <li>Enable non-duplicate gift check on <strong>Species</strong> and <Strong>Career</Strong> gift arrays</li>
      <li>Add gift favourite use fields</li>
      <li>Add natural weapons to battle array</li>
      <li>Enable <strong>Special Requirements</strong> functions (Will expand out when get to it)</li>
      <li>Add experience tracker</li>
      <li>Enable experience spend function (Will expand out when get to it)</li>
    </ol>
  </section>

  <section class="update-log">
    <h3>🛠️ Update 05/07/2025</h3>
    <p>The Character Builder has grown significantly. Here's what’s recently landed:</p>
    <ul>
      <li><strong>Free Choice System:</strong> Dynamic gift selectors now enforce dependencies, prerequisites, and duplication rules, ensuring valid character options with immediate feedback.</li>
      <li><strong>Character Persistence:</strong> Saved characters are now tied to individual user accounts via secure backend filtering, so only the original creator can view or edit them.</li>
      <li><strong>Custom Login & Registration:</strong> Seamless login and account creation now use branded pages styled like the rest of the site — no need to interact with the default WordPress login.</li>
      <li><strong>Skill Dice Display:</strong> The character summary now shows <em>all skills</em>, even those with no dice pool, giving a complete view of available capabilities.</li>
    </ul>

    <h4>🚧 In Progress</h4>
    <ul>
      <li><strong>Local Knowledge & Language Fields:</strong> Free-text inputs are now saved and restored reliably — even across tab switches and character loads.</li>
      <li>Character sheet styling enhancements to better match the official interactive PDF</li>
      <li>User onboarding improvements: email notifications and optional account approval</li>
      <li>Printable equipment and encumbrance summaries</li>
      <li><strong>Editable Summary & PDF Export:</strong> The summary view renders a printable character sheet, complete with boosted traits, dice pools, and bonuses — exportable as a PDF with one click.</li>
    </ul>

    <p>The builder is on its way to becoming usable.</p>
  </section>
</main>



} // namespace CharacterGeneratorDev
