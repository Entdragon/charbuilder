;(function($){
  let isDirty = false;
  CG_Species._loaded = false;
  CG_Career._loaded  = false;

  function showModal(){
    $('.cg-modal-overlay').fadeIn(200);
    const $tabs   = $('.cg-modal .cg-tabs li');
    const $panels = $('.cg-modal .tab-panel');
    $tabs.removeClass('active').first().addClass('active');
    $panels.removeClass('active').first().addClass('active');
    refreshTab($tabs.first().data('tab'));
  }

  function closeModal(){
    $('.cg-modal-overlay').fadeOut(200);
  }

  function goToTabByIndex(idx){
    const $tabs   = $('.cg-modal .cg-tabs li');
    const $panels = $('.cg-modal .tab-panel');
    const $t      = $tabs.eq(idx);
    if (!$t.length) return;

    $tabs.removeClass('active');
    $t.addClass('active');
    $panels.removeClass('active');
    $('#' + $t.data('tab')).addClass('active');
    refreshTab($t.data('tab'));
  }

  function refreshTab(tabId){
    switch(tabId) {
      case 'tab-traits':
        CG_Traits.enforceCounts();
        break;

      case 'tab-profile':
        if (!CG_Species._loaded) {
          CG_Species.loadSpeciesList(); CG_Species._loaded = true;
        }
        if (!CG_Career._loaded) {
          CG_Career.loadCareerList(); CG_Career._loaded = true;
        }
        CG_Gifts.loadFreeChoices();
        CG_Gifts.loadLocalKnowledge();
        CG_Gifts.loadLanguage();
        CG_Gifts.renderExtraCareerUI();
        break;

      case 'tab-skills':
        CG_Skills.refreshAll();
        break;

      case 'tab-summary':
        CG_Summary.renderSummary();
        break;
    }
  }

  function showUnsavedModal(){
    $('#cg-unsaved-confirm').removeClass('cg-hidden');
  }
  function hideUnsavedModal(){
    $('#cg-unsaved-confirm').addClass('cg-hidden');
  }

  $(function(){
    // Mark form dirty on any change
    $('body').on('input change', '#cg-form input, #cg-form select, #cg-form textarea', () => {
      isDirty = true;
    });

    // Open builder
    $('body').on('click', '#cg-open-builder', e => {
      e.preventDefault();
      isDirty = false;      // clear dirty flag before opening
      showModal();
    });

    // Close builder / overlay
    $('body').on('click', '.cg-modal-close, .cg-modal-overlay', function(e){
      if (e.target!==this && !$(this).hasClass('cg-modal-close')) return;
      e.preventDefault();
      if (isDirty) {
        showUnsavedModal();
        return;
      }
      closeModal();
      $('#cg-form-container').empty();
    });

    // Unsaved prompt actions
    $('body').on('click', '#unsaved-save', () => {
      hideUnsavedModal();
      $('#cg-form .cg-close-after-save').click();
    });
    $('body').on('click', '#unsaved-exit', () => {
      hideUnsavedModal();
      isDirty = false;
      closeModal();
      $('#cg-form-container').empty();
    });
    $('body').on('click', '#unsaved-cancel', () => {
      hideUnsavedModal();
    });

    // New Character
    $('body').on('click', '#cg-new', () => {
      CG_Gifts.selected     = [];
      CG_Gifts.freeLoaded   = false;
      CG_Skills.skillMarks  = {};
      CG_Species._loaded    = false;
      CG_Career._loaded     = false;
      isDirty               = false;

      $('#cg-form-container').html(
        `<form id="cg-form">${CG_FormBuilder.buildForm()}</form>`
      );

      setTimeout(() => {
        CG_Traits.enforceCounts();
        CG_Traits.updateAdjustedTraitDisplays();
        CG_Species.loadSpeciesList();
        CG_Career.loadCareerList();
        CG_Skills.loadSkillsList();
        goToTabByIndex(0);
      }, 50);
    });

    // Load Character list
    $('body').on('click', '#cg-load', () => {
      isDirty = false;
      $.post(CG_Ajax.ajax_url, {
        action:   'cg_load_characters',
        security: CG_Ajax.nonce
      }).done(res => {
        if (!res.success) return;
        let opts = '<option value="">— Select Character —</option>';
        res.data.forEach(c => opts += `<option value="${c.id}">${c.name}</option>`);
        $('#cg-form-container').html(`
          <label for="cg-select">Pick a character</label>
          <select id="cg-select">${opts}</select>
        `);
      });
    });

    // Character selected → load data
    $('body').on('change', '#cg-select', function(){
      const id = $(this).val();
      if (!id) return;

      $.post(CG_Ajax.ajax_url, {
        action:   'cg_get_character',
        id,
        security: CG_Ajax.nonce
      }).done(res => {
        if (!res.success) return;

        $('#cg-form-container').html(
          `<form id="cg-form">${CG_FormBuilder.buildForm(res.data)}</form>`
        );
        isDirty = false;

        setTimeout(() => {
          CG_Traits.enforceCounts();
          CG_Traits.updateAdjustedTraitDisplays();

          CG_Species.loadSpeciesList(() => {
            $('#cg-species').val(res.data.species).trigger('change');
          });
          CG_Career.loadCareerList(() => {
            $('#cg-career').val(res.data.career).trigger('change');
          });

          CG_Skills.loadSkillsList(() => {
            CG_Skills.loadMarkData(res.data.skill_marks||{});
            CG_Skills.refreshAll();
          });

          if (res.data.extra_career_1) {
            $('#cg-extra-career-1').val(res.data.extra_career_1).trigger('change');
          }
          if (res.data.extra_career_2) {
            $('#cg-extra-career-2').val(res.data.extra_career_2).trigger('change');
          }

          goToTabByIndex(0);
        }, 100);
      });
    });

    // Tab navigation within modal
    $('body').on('click', '.cg-nav-next, .cg-nav-prev', function(){
      const $tabs = $('.cg-modal .cg-tabs li');
      const idx   = $tabs.index($tabs.filter('.active')) +
                    ($(this).hasClass('cg-nav-next')? 1 : -1);
      goToTabByIndex(idx);
    });

    // Init handlers
    CG_Species.bindSpeciesHandler();
    CG_Career.bindCareerHandler();
    CG_Gifts.init();
    CG_Summary.bindExportButton();
  });
})(jQuery);
