// // handles rendering & events for “extra careers” dropdowns

// import FormBuilderAPI   from '../formBuilder';
// import CareerAPI        from './api.js';

// const $ = window.jQuery;
// let bound = false;

// const ExtraCareer = {
//   init() {
//     this.render();
//     if (!bound) {
//       this.bind();
//       bound = true;
//     }
//   },

//   render() {
//     const data  = FormBuilderAPI.getData();
//     const gifts = [
//       data.free_gift_1,
//       data.free_gift_2,
//       data.free_gift_3
//     ].filter(Boolean);

//     CareerAPI.loadEligibleExtraCareers(gifts, careers => {
//       const $wrap = $('#cg-extra-careers').empty();

//       careers.forEach((c, idx) => {
//         const sel = data[`extra_career_${idx}`] == c.id ? ' selected' : '';
//         const opts = careers
//           .map(item => `<option value="${item.id}"${data[\`extra_career_\${idx}\`]==item.id?' selected':''}>${item.name}</option>`)
//           .join('');

//         const block = `
//           <div class="cg-extra-career-block">
//             <label for="cg-extra-career-${idx}">Extra Career ${idx+1}</label>
//             <select id="cg-extra-career-${idx}" data-index="${idx}">
//               <option value="">— Select Career —</option>
//               ${opts}
//             </select>
//           </div>`;

//         $wrap.append(block);

//         // rehydrate existing selection’s skills
//         if (data[`extra_career_${idx}`]) {
//           this._addSkillRowsForCareer(idx, data[`extra_career_${idx}`]);
//         }
//       });
//     });
//   },

//   bind() {
//     $(document)
//       .off('change', '.cg-extra-career-block select')
//       .on('change', '.cg-extra-career-block select', e => {
//         const $sel      = $(e.currentTarget);
//         const idx       = $sel.data('index');
//         const careerId  = $sel.val();

//         // Persist selection
//         const state = FormBuilderAPI.getData();
//         FormBuilderAPI._data = {
//           ...state,
//           [`extra_career_${idx}`]: careerId
//         };

//         // Clear existing skill rows for this block
//         SkillsAPI.populateSkillDice(`extra_career_${idx}`, $('#cg-trait_career').val()||'', []);

//         // Load & render new skill rows
//         if (careerId) {
//           this._addSkillRowsForCareer(idx, careerId);
//         }
//       });
//   },

//   _addSkillRowsForCareer(idx, careerId) {
//     CareerAPI.loadGifts(careerId, profile => {
//       const die = $('#cg-trait_career').val() || '';
//       const ids = [
//         profile.skill_one,
//         profile.skill_two,
//         profile.skill_three
//       ].map(Number).filter(n => !isNaN(n));

//       SkillsAPI.populateSkillDice(`extra_career_${idx}`, die, ids);
//     });
//   }
// };

// export default ExtraCareer;
