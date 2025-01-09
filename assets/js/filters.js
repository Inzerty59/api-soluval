
document.getElementById('toggle-advanced-filters').addEventListener('click', function () {
    const advancedFilters = document.getElementById('advanced-filters');
    if (advancedFilters.classList.contains('hidden')) {
        advancedFilters.classList.remove('hidden');
    } else {
        advancedFilters.classList.add('hidden');
    }
});
