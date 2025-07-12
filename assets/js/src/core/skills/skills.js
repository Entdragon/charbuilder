;(function($){
  window.CG_Skills = {
    remainingMarks:    13,
    skillMarks:        {},
    speciesSkillIds:   [],
    careerSkillIds:    [],
    extraCareerSkills: {},

    init: function(){
      this.bindMarkHandlers();

      // Only load when user clicks the Skills tab
      $('#cg-app').on('click', '[data-tab="tab-skills"]', () => {
        this.loadSkillsList();
      });

      // Refresh dice‐pools live if extra‐career traits change
      $('#cg-app').on('change', '.cg-trait-career-select', () => {
        this.refreshAll();
      });
    },

    loadSkillsList: function(callback){
      const self = this;
      const $tbl = $('#skills-table');
      if (!$tbl.length) {
        if (callback) callback();
        return;
      }

      $.post(CG_Ajax.ajax_url, {
        action:   'cg_get_skills_list',
        security: CG_Ajax.nonce
      }).done(res => {
        if (!res.success) {
          if (callback) callback();
          return;
        }

        const $thead = $tbl.find('thead tr').empty();
        const $tbody = $tbl.find('tbody').empty();

        // --- Headers ---
        let sp = $('#cg-species option:selected').text().trim();
        if (/select/i.test(sp)) sp = '';
        let cr = $('#cg-career option:selected').text().trim();
        if (/select/i.test(cr)) cr = '';

        $thead.append('<th>Skill</th>');
        $thead.append(`<th>Species${sp?': '+sp:''}</th>`);
        $thead.append(`<th>Career${cr?': '+cr:''}</th>`);

        const extraCount = $('.cg-extra-career-block').length;
        for (let i=1; i<=extraCount; i++){
          let nm = $(`#cg-extra-career-${i} option:selected`).text().trim();
          if (/select/i.test(nm)) nm = '';
          const lbl = nm? `Career: ${nm}` : `Career ${i+1}`;
          $thead.append(`<th>${lbl}</th>`);
        }
        $thead.append('<th>Marks</th><th>Dice Pool</th>');

        // --- Rows ---
        res.data.forEach(skill => {
          const id    = skill.id;
          const cnt   = self.skillMarks[id]||0;
          const dice  = self.diceFromMarks(cnt);

          const btns = [1,2,3].map(n=>
            `<button data-mark="${n}" class="${n<=cnt?'active':''}">•</button>`
          ).join('');

          let cols = `
            <td>${skill.ct_skill_name}</td>
            <td class="skill-species"></td>
            <td class="skill-career"></td>
          `;
          for (let i=1; i<=extraCount; i++){
            cols += `<td class="skill-extra skill-extra-${i}"></td>`;
          }
          cols += `
            <td class="skill-marks">
              <div class="mark-buttons" data-skill-id="${id}">
                ${btns}
              </div>
              <div class="mark-dice">${dice||'—'}</div>
            </td>
            <td class="skill-total"></td>
          `;
          $tbody.append(`<tr data-skill-id="${id}">${cols}</tr>`);
        });

        if (!$('#remaining-marks').length) {
          $tbl.after('<p id="remaining-marks">Marks Left: 13</p>');
        }

        // Re‐scan extras then update
        setTimeout(()=>{
          self.scanExtraCareers(() => {
            self.updateMarkButtons();
            self.updateRemainingMarks();
            self.refreshAll();
          });
        },50);

        if (callback) callback();
      });
    },

    diceFromMarks(n){
      return n===1?'d4':n===2?'d6':n===3?'d8':'';
    },

    totalMarksExcept(ex){
      return Object.entries(this.skillMarks)
        .filter(([id])=>+id!==+ex)
        .reduce((sum,[,v])=>sum+v,0);
    },

    updateRemainingMarks(){
      this.remainingMarks = 13 - Object.values(this.skillMarks).reduce((a,b)=>a+b,0);
      $('#remaining-marks').text(`Marks Left: ${this.remainingMarks}`);
    },

    calculateDicePool($row, markDice){
      const parts = [];
      $row.find('td.skill-species, td.skill-career, td.skill-extra')
        .each(function(){
          const t = $(this).text().trim();
          if (t) parts.push(t);
        });
      if (markDice) parts.push(markDice);
      return parts.join(' + ');
    },

    /**
     * Restored so species.js / career.js can feed in skill IDs.
     */
    populateSkillDice(source, _die, skillIds=[]){
      if (source==='species') this.speciesSkillIds = skillIds;
      if (source==='career')  this.careerSkillIds  = skillIds;
      this.refreshAll();
    },

    scanExtraCareers(callback){
      this.extraCareerSkills = {};
      const self = this;
      let pending = 0;

      $('.cg-extra-career-block').each((i)=>{
        const idx = i+1;
        const cid = $(`#cg-extra-career-${idx}`).val();
        if (!cid) return;

        pending++;
        $.post(CG_Ajax.ajax_url, {
          action:   'cg_get_career_gifts',
          id:       cid,
          security: CG_Ajax.nonce
        }).done(res=>{
          if (res.success){
            const ids = [res.data.skill_one,res.data.skill_two,res.data.skill_three]
              .map(Number).filter(n=>!isNaN(n));
            self.extraCareerSkills[idx] = ids;
          }
        }).always(()=>{
          if (--pending===0 && typeof callback==='function') callback();
        });
      });

      if (pending===0 && typeof callback==='function') callback();
    },

    refreshAll(){
      $('#skills-table tbody tr').each((_,row)=>{
        const $row   = $(row);
        const sid    = +$row.data('skill-id');
        const marks  = this.skillMarks[sid]||0;
        const markD  = this.diceFromMarks(marks);

        // species die
        const sp = this.speciesSkillIds.includes(sid)
          ? (CG_Traits.getBoostedDie('trait_species')||$('#cg-trait_species').val()||'')
          : '';
        $row.find('.skill-species').text(sp);

        // primary career die
        const cr = this.careerSkillIds.includes(sid)
          ? (CG_Traits.getBoostedDie('trait_career')||$('#cg-trait_career').val()||'')
          : '';
        $row.find('.skill-career').text(cr);

        // extras: boosted or base select
        Object.entries(this.extraCareerSkills).forEach(([i,ids])=>{
          let out = '';
          if (ids.includes(sid)){
            const key   = `trait_career_${i}`;
            const boost = CG_Traits.getBoostedDie(key);
            const base  = $(`#cg-${key}`).val()||'';
            out = boost||base||'';
          }
          $row.find(`.skill-extra-${i}`).text(out);
        });

        $row.find('.mark-dice').text(markD||'—');
        $row.find('.skill-total').text(this.calculateDicePool($row,markD));
      });
    },

    bindMarkHandlers(){
      $('#cg-app').on('click','.mark-buttons button', e=>{
        const $b  = $(e.currentTarget);
        const sid = +$b.closest('.mark-buttons').data('skill-id');
        const sel = +$b.data('mark');
        const cur = this.skillMarks[sid]||0;
        const nxt = cur===sel?0:sel;
        if (this.totalMarksExcept(sid)+nxt>13) return;
        this.skillMarks[sid] = nxt;

        $b.siblings().addBack().each((_,btn)=>{
          const m = +$(btn).data('mark');
          $(btn).toggleClass('active', m<=nxt);
        });

        const d = this.diceFromMarks(nxt);
        const $row = $b.closest('tr');
        $row.find('.mark-dice').text(d||'—');
        $row.find('.skill-total').text(this.calculateDicePool($row,d));
        this.updateRemainingMarks();
      });
    },

    updateMarkButtons(){
      $('#skills-table tbody tr').each((_,row)=>{
        const $r = $(row);
        const sid = +$r.data('skill-id');
        const m   = this.skillMarks[sid]||0;
        $r.find('.mark-buttons button').each((_,btn)=>{
          const v = +$(btn).data('mark');
          $(btn).toggleClass('active', v<=m);
        });
      });
    },

    collectMarkData(){ return this.skillMarks; },
    loadMarkData(saved){
      this.skillMarks = {};
      Object.entries(saved||{}).forEach(([id,v])=>{
        this.skillMarks[+id] = +v;
      });
      this.updateMarkButtons();
      this.updateRemainingMarks();
    }
  };

  $(function(){ CG_Skills.init(); });
})(jQuery);
