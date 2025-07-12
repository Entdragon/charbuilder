;(function($){
  window.CG_GiftLibrary = {
    gifts: {},

    register(giftList) {
      console.log('[CG_GiftLibrary] Registering gifts:', giftList);
      giftList.forEach(g => {
        this.gifts[+g.id] = g;
      });
    },

    allowsMultiple(id) {
      const g = this.gifts[+id];
      const result = g?.ct_gifts_allows_multiple == 1 || g?.ct_gifts_manifold == 1;
      console.log('[CG_GiftLibrary] Gift', id, 'allows multiple:', result);
      return result;
    },

    isIncreaseTrait(id) {
      const g = this.gifts[+id];
      const result = g?.ct_gifts_type === 'Increase Trait';
      console.log('[CG_GiftLibrary] Gift', id, 'is Increase Trait:', result);
      return result;
    },

isExcludedFromFreeSelection(id, selectedIds = []) {
  const g = this.gifts[+id];
  if (!g) return false;

  const alreadySelected = selectedIds.includes(+id);
  const isAllowedMultiple = g.ct_gifts_allows_multiple == 1 || g.ct_gifts_manifold == 1;

  const reqFields = [
    'ct_gifts_requires',
    'ct_gifts_requires_two',
    'ct_gifts_requires_three',
    'ct_gifts_requires_four',
    'ct_gifts_requires_five',
    'ct_gifts_requires_six',
    'ct_gifts_requires_seven',
    'ct_gifts_requires_eight',
    'ct_gifts_requires_nine',
    'ct_gifts_requires_ten',
    'ct_gifts_requires_eleven',
    'ct_gifts_requires_twelve',
    'ct_gifts_requires_thirteen',
    'ct_gifts_requires_fourteen',
    'ct_gifts_requires_fifteen',
    'ct_gifts_requires_sixteen',
    'ct_gifts_requires_seventeen',
    'ct_gifts_requires_eighteen',
    'ct_gifts_requires_nineteen'
  ];

  const requiresIds = reqFields
    .map(f => +g[f])
    .filter(v => !isNaN(v) && v > 0);

  const hasRequirementData = requiresIds.length > 0;
  const hasConflicts = requiresIds.some(req => selectedIds.includes(req));

  console.log(`[CG_GiftLibrary] Evaluating gift ${id}`, {
    alreadySelected,
    isAllowedMultiple,
    requiresIds,
    hasRequirementData,
    hasConflicts,
    selectedIds
  });

  // If there are requirements but none are selected, exclude
  if (hasRequirementData && selectedIds.length === 0) return true;

  // If selected already but not allowed to duplicate, exclude
  if (alreadySelected && !isAllowedMultiple) return true;

  // If there’s a requirement conflict, exclude
  if (hasConflicts) return true;

  return false;
}

  };
})(jQuery);
