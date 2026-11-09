(() => {
    const listPanel = document.querySelector('.page-wrap .list-panel');
    if (listPanel && listPanel.querySelector('.table-wrap')) {
        const url = new URL(window.location.href);
        if (url.searchParams.get('per_page') !== '20') {
            url.searchParams.set('per_page', '20');
            window.location.replace(url);
            return;
        }
    }

    document.querySelectorAll('[data-create-modal]').forEach((trigger) => {
        const dialog = document.getElementById(trigger.dataset.createModal);
        if (!dialog) return;
        trigger.addEventListener('click', () => {
            dialog.showModal();
            dialog.querySelector('input:not([type="hidden"]), select')?.focus();
        });
        dialog.querySelectorAll('[data-create-modal-close]').forEach((button) => {
            button.addEventListener('click', () => dialog.close());
        });
        dialog.addEventListener('click', (event) => {
            if (event.target !== dialog) return;
            const rect = dialog.getBoundingClientRect();
            if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close();
        });
        dialog.addEventListener('close', () => trigger.focus());
    });

    const toggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const close = document.querySelector('.menu-close');
    const backdrop = document.querySelector('.nav-backdrop');
    const mobile = window.matchMedia('(max-width: 900px)');
    const getDropdowns = () => document.querySelectorAll('.review-details, .add-details, .account-dropdown');

    document.addEventListener('click', (event) => {
        getDropdowns().forEach((details) => {
            if (details.open && !details.contains(event.target)) {
                details.open = false;
            }
        });
    });

    getDropdowns().forEach((details) => {
        details.addEventListener('toggle', () => {
            const tr = details.closest('tr');
            if (details.open) {
                getDropdowns().forEach((other) => {
                    if (other !== details && other.open) other.open = false;
                });
                if (tr) tr.classList.add('has-open-dropdown');
                const popover = details.querySelector('.review-form, .student-actions, .add-program-form, .account-panel');
                if (popover && window.innerWidth > 760) {
                    popover.classList.remove('dropup');
                    popover.style.maxHeight = '';
                    popover.style.left = '';
                    popover.style.right = '';

                    const summary = details.querySelector('summary');
                    const summaryRect = summary ? summary.getBoundingClientRect() : popover.getBoundingClientRect();
                    const rect = popover.getBoundingClientRect();
                    const spaceBelow = window.innerHeight - summaryRect.bottom;
                    const spaceAbove = summaryRect.top;

                    if ((rect.bottom > window.innerHeight - 16 || spaceBelow < rect.height) && spaceAbove > spaceBelow) {
                        popover.classList.add('dropup');
                    }

                    const isDropup = popover.classList.contains('dropup');
                    const availableSpace = isDropup ? (spaceAbove - 20) : (spaceBelow - 20);
                    if (availableSpace > 140) {
                        popover.style.maxHeight = `${Math.min(560, Math.floor(availableSpace))}px`;
                    }

                    const updatedRect = popover.getBoundingClientRect();
                    if (updatedRect.left < 16) {
                        popover.style.right = 'auto';
                        popover.style.left = '0';
                    } else if (updatedRect.right > window.innerWidth - 16) {
                        popover.style.left = 'auto';
                        popover.style.right = '0';
                    }
                }
            } else {
                if (tr) tr.classList.remove('has-open-dropdown');
                const popover = details.querySelector('.review-form, .student-actions, .add-program-form, .account-panel');
                if (popover) {
                    popover.classList.remove('dropup');
                    popover.style.maxHeight = '';
                    popover.style.left = '';
                    popover.style.right = '';
                }
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            getDropdowns().forEach((details) => {
                if (details.open) {
                    details.open = false;
                    const summary = details.querySelector('summary');
                    if (summary) summary.focus();
                }
            });
        }
    });
    const setOpen = (open, restoreFocus = true) => {
        document.body.classList.toggle('nav-open', open);
        if (toggle) {
            toggle.setAttribute('aria-expanded', String(open));
            toggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
        }
        if (backdrop) backdrop.hidden = !open;
        if (open) {
            const account = document.querySelector('.account-dropdown');
            if (account) account.open = false;
            if (close) close.focus();
        } else if (restoreFocus && toggle) {
            toggle.focus();
        }
    };
    document.body.classList.add('nav-ready');
    if (toggle) toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
    if (close) close.addEventListener('click', () => setOpen(false));
    if (backdrop) backdrop.addEventListener('click', () => setOpen(false));
    mobile.addEventListener('change', () => setOpen(false, false));
    document.addEventListener('keydown', (event) => {
        if (!document.body.classList.contains('nav-open')) return;
        if (event.key === 'Escape') setOpen(false);
        if (event.key === 'Tab') {
            const items = sidebar.querySelectorAll('a, button');
            const first = items[0], last = items[items.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });

    // ==========================================================================
    // Grade Encoding Modal Setup
    // ==========================================================================
    const gradeModal = document.getElementById('grade-encoding-modal');
    if (gradeModal) {
        const modalForm = document.getElementById('grade-modal-form');
        const modalTitle = document.getElementById('grade-modal-title');
        const modalSubtitle = document.getElementById('grade-modal-subtitle');
        const midtermInput = document.getElementById('modal-midterm');
        const finalInput = document.getElementById('modal-final-term');
        const computedGradeEl = document.getElementById('modal-computed-grade');
        const computedBadgeEl = document.getElementById('modal-computed-badge');
        const closeBtn = document.getElementById('grade-modal-close');
        const cancelBtn = document.getElementById('grade-modal-cancel');

        const updateComputedGrade = () => {
            const m = midtermInput.valueAsNumber;
            const f = finalInput.valueAsNumber;
            const complete = midtermInput.value !== '' && finalInput.value !== '' && midtermInput.validity.valid && finalInput.validity.valid;
            const avg = complete ? Math.round(((m + f) / 2) * 100) / 100 : null;
            computedGradeEl.textContent = avg === null ? '\u2014' : avg.toFixed(2);
            const status = avg === null ? 'In progress' : avg <= 3 ? 'Passed' : 'Failed';
            computedBadgeEl.textContent = status;
            computedBadgeEl.className = 'status-badge status-' + ({Incomplete: 'incomplete', Passed: 'approved', Failed: 'rejected', 'In progress': 'under_review'}[status]);
        };

        const openGradeModal = (source) => {
            const action = source.dataset.action || '';
            const code = source.dataset.subjectCode || '';
            const title = source.dataset.subjectTitle || '';
            const units = source.dataset.subjectUnits || '';
            const midterm = source.dataset.midterm || '';
            const finalTerm = source.dataset.final || '';
            const remarks = source.dataset.remarks || 'In progress';

            if (modalForm) modalForm.action = action;
            if (modalTitle) modalTitle.textContent = code;
            if (modalSubtitle) modalSubtitle.textContent = units ? `${title} · ${units} units` : title;

            if (midtermInput) midtermInput.value = midterm;
            if (finalInput) finalInput.value = finalTerm;
            updateComputedGrade();

            if (typeof gradeModal.showModal === 'function') {
                gradeModal.showModal();
            } else {
                gradeModal.setAttribute('open', '');
            }

            if (midtermInput) midtermInput.focus();
        };

        // Click anywhere on subject row or action button to open grade modal
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('.clickable-grade-row, .open-grade-modal-btn');
            if (trigger && !e.target.closest('button:not(.open-grade-modal-btn), a, input, select, form')) {
                openGradeModal(trigger);
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                const row = e.target.closest('.clickable-grade-row');
                if (row && !e.target.closest('button, a, input, select, form')) {
                    e.preventDefault();
                    openGradeModal(row);
                }
            }
        });

        const closeModal = () => {
            if (typeof gradeModal.close === 'function') {
                gradeModal.close();
            } else {
                gradeModal.removeAttribute('open');
            }
        };

        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        gradeModal.addEventListener('click', (e) => {
            if (e.target === gradeModal) {
                closeModal();
            }
        });

        [midtermInput, finalInput].forEach((input) => {
            if (input) input.addEventListener('input', updateComputedGrade);
        });
    }

    // ==========================================================================
    // Subject Edit Modal Setup
    // ==========================================================================
    const subjectModal = document.getElementById('subject-edit-modal');
    if (subjectModal) {
        const subjectEditForm = document.getElementById('subject-edit-form');
        const subjectToggleForm = document.getElementById('subject-toggle-form');
        const modalTitle = document.getElementById('subject-modal-title');
        const modalSubtitle = document.getElementById('subject-modal-subtitle');
        const programSelect = document.getElementById('subject-modal-program');
        const majorSelect = document.getElementById('subject-modal-major');
        const codeInput = document.getElementById('subject-modal-code');
        const titleInput = document.getElementById('subject-modal-title-input');
        const unitsInput = document.getElementById('subject-modal-units');
        const yearSelect = document.getElementById('subject-modal-year');
        const semesterSelect = document.getElementById('subject-modal-semester');
        const toggleBtn = document.getElementById('subject-toggle-btn');
        const closeBtn = document.getElementById('subject-modal-close');
        const cancelBtn = document.getElementById('subject-modal-cancel');

        const openSubjectModal = (row) => {
            const action = row.dataset.action || '';
            const toggleAction = row.dataset.toggleAction || '';
            const code = row.dataset.code || '';
            const title = row.dataset.title || '';
            const programId = row.dataset.programId || '';
            const majorId = row.dataset.majorId || '';
            const units = row.dataset.units || '3';
            const yearLevel = row.dataset.yearLevel || '1';
            const semester = row.dataset.semester || '1st';
            const isActive = row.dataset.isActive === '1';

            if (subjectEditForm) subjectEditForm.action = action;
            if (subjectToggleForm) subjectToggleForm.action = toggleAction;

            if (modalTitle) modalTitle.textContent = code;
            if (modalSubtitle) modalSubtitle.textContent = title;

            if (programSelect) programSelect.value = programId;
            if (majorSelect) majorSelect.value = majorId;
            if (codeInput) codeInput.value = code;
            if (titleInput) titleInput.value = title;
            if (unitsInput) unitsInput.value = units;
            if (yearSelect) yearSelect.value = yearLevel;
            if (semesterSelect) semesterSelect.value = semester;

            if (toggleBtn) {
                toggleBtn.textContent = isActive ? 'Archive subject' : 'Restore subject';
                toggleBtn.className = isActive ? 'button button-quiet button-small text-danger' : 'button button-quiet button-small';
            }

            if (typeof subjectModal.showModal === 'function') {
                subjectModal.showModal();
            } else {
                subjectModal.setAttribute('open', '');
            }

            if (titleInput) titleInput.focus();
        };

        const closeSubjectModal = () => {
            if (typeof subjectModal.close === 'function') {
                subjectModal.close();
            } else {
                subjectModal.removeAttribute('open');
            }
        };

        // Event delegation: works even after table content is updated via realtime AJAX
        document.addEventListener('click', (e) => {
            const row = e.target.closest('.clickable-subject-row');
            if (row && !e.target.closest('button, a, input, select, form')) {
                openSubjectModal(row);
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                const row = e.target.closest('.clickable-subject-row');
                if (row && !e.target.closest('button, a, input, select, form')) {
                    e.preventDefault();
                    openSubjectModal(row);
                }
            }
        });

        if (toggleBtn && subjectToggleForm) {
            toggleBtn.addEventListener('click', () => {
                if (confirm('Are you sure you want to change the active status of this subject?')) {
                    subjectToggleForm.submit();
                }
            });
        }

        if (closeBtn) closeBtn.addEventListener('click', closeSubjectModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeSubjectModal);

        subjectModal.addEventListener('click', (e) => {
            if (e.target === subjectModal) {
                closeSubjectModal();
            }
        });
    }

    // ==========================================================================
    // Student Edit Modal Setup
    // ==========================================================================
    const studentModal = document.getElementById('student-edit-modal');
    if (studentModal) {
        const studentEditForm = document.getElementById('student-edit-form');
        const studentAccessForm = document.getElementById('student-access-form');
        const modalTitle = document.getElementById('student-modal-title');
        const modalSubtitle = document.getElementById('student-modal-subtitle');
        const numberInput = document.getElementById('student-modal-number');
        const emailInput = document.getElementById('student-modal-email');
        const firstNameInput = document.getElementById('student-modal-first-name');
        const lastNameInput = document.getElementById('student-modal-last-name');
        const accessBtn = document.getElementById('student-access-btn');
        const closeBtn = document.getElementById('student-modal-close');
        const cancelBtn = document.getElementById('student-modal-cancel');

        const openStudentModal = (row) => {
            const action = row.dataset.action || '';
            const accessAction = row.dataset.accessAction || '';
            const studentNumber = row.dataset.studentNumber || '';
            const firstName = row.dataset.firstName || '';
            const lastName = row.dataset.lastName || '';
            const email = row.dataset.email || '';
            const program = row.dataset.program || '';
            const isActive = row.dataset.isActive === '1';
            const joined = row.dataset.joined || '';

            if (studentEditForm) studentEditForm.action = action;
            if (studentAccessForm) studentAccessForm.action = accessAction;

            if (modalTitle) modalTitle.textContent = `${firstName} ${lastName}`.trim() || 'Student Profile';

            const subtitleParts = [];
            if (studentNumber) subtitleParts.push(studentNumber);
            if (program) subtitleParts.push(program);
            if (joined) subtitleParts.push(`Joined ${joined}`);
            if (modalSubtitle) modalSubtitle.textContent = subtitleParts.join(' · ') || 'Student Account';

            if (numberInput) numberInput.value = studentNumber;
            if (emailInput) emailInput.value = email;
            if (firstNameInput) firstNameInput.value = firstName;
            if (lastNameInput) lastNameInput.value = lastName;

            if (accessBtn) {
                accessBtn.textContent = isActive ? 'Deactivate account' : 'Restore access';
                accessBtn.className = isActive ? 'button button-quiet button-small text-danger' : 'button button-quiet button-small';
            }

            if (typeof studentModal.showModal === 'function') {
                studentModal.showModal();
            } else {
                studentModal.setAttribute('open', '');
            }

            if (numberInput) numberInput.focus();
        };

        const closeStudentModal = () => {
            if (typeof studentModal.close === 'function') {
                studentModal.close();
            } else {
                studentModal.removeAttribute('open');
            }
        };

        // Event delegation: full row click opens student modal
        document.addEventListener('click', (e) => {
            const row = e.target.closest('.clickable-student-profile-row');
            if (row && !e.target.closest('button, a, input, select, form')) {
                openStudentModal(row);
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                const row = e.target.closest('.clickable-student-profile-row');
                if (row && !e.target.closest('button, a, input, select, form')) {
                    e.preventDefault();
                    openStudentModal(row);
                }
            }
        });

        if (accessBtn && studentAccessForm) {
            accessBtn.addEventListener('click', () => {
                if (confirm('Are you sure you want to change this student\'s account status?')) {
                    studentAccessForm.submit();
                }
            });
        }

        if (closeBtn) closeBtn.addEventListener('click', closeStudentModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeStudentModal);

        studentModal.addEventListener('click', (e) => {
            if (e.target === studentModal) {
                closeStudentModal();
            }
        });
    }

    // ==========================================================================
    // Program Edit Modal Setup
    // ==========================================================================
    const programModal = document.getElementById('program-edit-modal');
    if (programModal) {
        const programEditForm = document.getElementById('program-edit-form');
        const programToggleForm = document.getElementById('program-toggle-form');
        const modalTitle = document.getElementById('program-modal-title');
        const modalSubtitle = document.getElementById('program-modal-subtitle');
        const codeInput = document.getElementById('program-modal-code');
        const nameInput = document.getElementById('program-modal-name');
        const departmentInput = document.getElementById('program-modal-department');
        const durationSelect = document.getElementById('program-modal-duration');
        const toggleBtn = document.getElementById('program-toggle-btn');
        const closeBtn = document.getElementById('program-modal-close');
        const cancelBtn = document.getElementById('program-modal-cancel');

        const openProgramModal = (row) => {
            const action = row.dataset.action || '';
            const toggleAction = row.dataset.toggleAction || '';
            const code = row.dataset.code || '';
            const name = row.dataset.name || '';
            const department = row.dataset.department || '';
            const duration = row.dataset.duration || '4';
            const isActive = row.dataset.isActive === '1';

            if (programEditForm) programEditForm.action = action;
            if (programToggleForm) programToggleForm.action = toggleAction;

            if (modalTitle) modalTitle.textContent = code;
            if (modalSubtitle) modalSubtitle.textContent = name;

            if (codeInput) codeInput.value = code;
            if (nameInput) nameInput.value = name;
            if (departmentInput) departmentInput.value = department;
            if (durationSelect) durationSelect.value = duration;

            if (toggleBtn) {
                toggleBtn.textContent = isActive ? 'Archive program' : 'Reopen program';
                toggleBtn.className = isActive ? 'button button-quiet button-small text-danger' : 'button button-quiet button-small';
            }

            if (typeof programModal.showModal === 'function') {
                programModal.showModal();
            } else {
                programModal.setAttribute('open', '');
            }

            if (nameInput) nameInput.focus();
        };

        const closeProgramModal = () => {
            if (typeof programModal.close === 'function') {
                programModal.close();
            } else {
                programModal.removeAttribute('open');
            }
        };

        // Event delegation: full row click opens program modal
        document.addEventListener('click', (e) => {
            const row = e.target.closest('.clickable-program-row');
            if (row && !e.target.closest('button, a, input, select, form')) {
                openProgramModal(row);
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                const row = e.target.closest('.clickable-program-row');
                if (row && !e.target.closest('button, a, input, select, form')) {
                    e.preventDefault();
                    openProgramModal(row);
                }
            }
        });

        if (toggleBtn && programToggleForm) {
            toggleBtn.addEventListener('click', () => {
                if (confirm('Are you sure you want to change the status of this program?')) {
                    programToggleForm.submit();
                }
            });
        }

        if (closeBtn) closeBtn.addEventListener('click', closeProgramModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeProgramModal);

        programModal.addEventListener('click', (e) => {
            if (e.target === programModal) {
                closeProgramModal();
            }
        });
    }

    // ==========================================================================
    // Realtime Subjects Filter & Pagination
    // ==========================================================================
    const subjectsFilterForm = document.querySelector('.subjects-filter-row');
    const subjectsPanel = document.querySelector('.panel.list-panel');

    if (subjectsFilterForm && subjectsPanel) {
        let activeController = null;

        const updateSubjectsContent = async (url, pushState = true) => {
            if (activeController) {
                activeController.abort();
            }
            activeController = new AbortController();

            const tableWrap = subjectsPanel.querySelector('.table-wrap');
            const paginationRow = subjectsPanel.querySelector('.pagination-row');
            const filterActions = subjectsFilterForm.querySelector('.grades-filter-actions');

            if (tableWrap) {
                tableWrap.style.opacity = '0.45';
                tableWrap.style.transition = 'opacity 0.15s ease';
            }

            try {
                const response = await fetch(url, {
                    signal: activeController.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Network error');

                const text = await response.text();
                const doc = new DOMParser().parseFromString(text, 'text/html');

                const newTableWrap = doc.querySelector('.panel.list-panel .table-wrap');
                const newPagination = doc.querySelector('.panel.list-panel .pagination-row');

                if (newTableWrap && tableWrap) {
                    tableWrap.innerHTML = newTableWrap.innerHTML;
                }
                if (newPagination && paginationRow) {
                    paginationRow.innerHTML = newPagination.innerHTML;
                }

                if (pushState) {
                    window.history.pushState({ path: url }, '', url);
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    console.error('Filter fetch error:', err);
                    subjectsFilterForm.submit();
                }
            } finally {
                if (tableWrap) tableWrap.style.opacity = '1';
            }
        };

        const triggerFilter = () => {
            const formData = new FormData(subjectsFilterForm);
            const params = new URLSearchParams();

            for (const [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }

            const baseUrl = subjectsFilterForm.action.split('?')[0];
            const queryString = params.toString();
            const url = queryString ? `${baseUrl}?${queryString}` : baseUrl;
            updateSubjectsContent(url);
        };

        // Real-time instant filtering on dropdown change
        subjectsFilterForm.querySelectorAll('select').forEach((select) => {
            select.addEventListener('change', triggerFilter);
        });

        // Intercept form submission
        subjectsFilterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            triggerFilter();
        });

        // Intercept pagination clicks for instant page changes
        subjectsPanel.addEventListener('click', (e) => {
            const pageLink = e.target.closest('.pagination-row a');
            if (pageLink && pageLink.href) {
                e.preventDefault();
                updateSubjectsContent(pageLink.href);
            }
        });

        // Handle browser back/forward buttons
        window.addEventListener('popstate', () => {
            updateSubjectsContent(window.location.href, false);
            const params = new URLSearchParams(window.location.search);
            const progSelect = subjectsFilterForm.querySelector('#program-filter');
            const yrSelect = subjectsFilterForm.querySelector('#year-filter');
            const semSelect = subjectsFilterForm.querySelector('#semester-filter');

            if (progSelect) progSelect.value = params.get('program_id') || '';
            if (yrSelect) yrSelect.value = params.get('year_level') || '';
            if (semSelect) semSelect.value = params.get('semester') || '';
        });
    }

    // ==========================================================================
    // Student Directory Click Navigation
    // ==========================================================================
    document.addEventListener('click', (e) => {
        const studentRow = e.target.closest('.clickable-student-row');
        if (studentRow && !e.target.closest('button, a, input, select, form')) {
            const href = studentRow.dataset.href;
            if (href) window.location = href;
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            const studentRow = e.target.closest('.clickable-student-row');
            if (studentRow && !e.target.closest('button, a, input, select, form')) {
                e.preventDefault();
                const href = studentRow.dataset.href;
                if (href) window.location = href;
            }
        }
    });

    // ==========================================================================
    // Realtime Grades Directory Filter & AJAX Pagination
    // ==========================================================================
    const gradesFilterForm = document.querySelector('.grades-filter-row');
    const gradesDirectoryPanel = document.querySelector('.panel.list-panel');

    if (gradesFilterForm && gradesDirectoryPanel) {
        let activeController = null;
        let searchDebounceTimer = null;

        const updateGradesContent = async (url, pushState = true) => {
            if (activeController) {
                activeController.abort();
            }
            activeController = new AbortController();

            const tableWrap = gradesDirectoryPanel.querySelector('.table-wrap');
            const paginationRow = gradesDirectoryPanel.querySelector('.pagination-row');
            const emptyState = gradesDirectoryPanel.querySelector('.empty-state');

            if (tableWrap) {
                tableWrap.style.opacity = '0.45';
                tableWrap.style.transition = 'opacity 0.15s ease';
            }

            try {
                const response = await fetch(url, {
                    signal: activeController.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Network error');

                const text = await response.text();
                const doc = new DOMParser().parseFromString(text, 'text/html');

                const newTableWrap = doc.querySelector('.panel.list-panel .table-wrap');
                const newPagination = doc.querySelector('.panel.list-panel .pagination-row');
                const newEmptyState = doc.querySelector('.panel.list-panel .empty-state');

                if (newTableWrap) {
                    if (tableWrap) {
                        tableWrap.innerHTML = newTableWrap.innerHTML;
                        tableWrap.style.display = '';
                        if (emptyState) emptyState.remove();
                    } else if (emptyState) {
                        emptyState.replaceWith(newTableWrap);
                    }
                    if (paginationRow && newPagination) {
                        paginationRow.innerHTML = newPagination.innerHTML;
                        paginationRow.style.display = '';
                    } else if (paginationRow && !newPagination) {
                        paginationRow.style.display = 'none';
                    } else if (!paginationRow && newPagination) {
                        gradesDirectoryPanel.append(newPagination);
                    }
                } else if (newEmptyState) {
                    if (tableWrap) tableWrap.style.display = 'none';
                    if (paginationRow) paginationRow.style.display = 'none';
                    if (emptyState) {
                        emptyState.innerHTML = newEmptyState.innerHTML;
                        emptyState.style.display = '';
                    } else {
                        gradesDirectoryPanel.insertAdjacentElement('beforeend', newEmptyState);
                    }
                }

                if (pushState) {
                    window.history.pushState({ path: url }, '', url);
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    console.error('Grades filter fetch error:', err);
                }
            } finally {
                const curTableWrap = gradesDirectoryPanel.querySelector('.table-wrap');
                if (curTableWrap) curTableWrap.style.opacity = '1';
            }
        };

        const triggerGradesFilter = () => {
            const formData = new FormData(gradesFilterForm);
            const params = new URLSearchParams();

            const searchVal = formData.get('search');
            const programVal = formData.get('program_id');

            if (searchVal) params.append('search', searchVal);
            if (programVal) params.append('program_id', programVal);

            const baseUrl = gradesFilterForm.action.split('?')[0];
            const queryString = params.toString();
            const url = queryString ? `${baseUrl}?${queryString}` : baseUrl;
            updateGradesContent(url);
        };

        const searchInput = gradesFilterForm.querySelector('#search-student');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(triggerGradesFilter, 250);
            });
        }

        const programFilter = gradesFilterForm.querySelector('#program-filter');
        if (programFilter) {
            programFilter.addEventListener('change', triggerGradesFilter);
        }



        gradesFilterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            triggerGradesFilter();
        });

        gradesDirectoryPanel.addEventListener('click', (e) => {
            const pageLink = e.target.closest('.pagination-row a');
            if (pageLink && pageLink.href) {
                e.preventDefault();
                updateGradesContent(pageLink.href);
            }
        });
    }
})();
