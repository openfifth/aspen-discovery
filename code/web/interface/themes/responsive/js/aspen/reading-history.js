AspenDiscovery.Account.ReadingHistory = (function(){
	return {
		initAccordions() {
			const updateToggleLabel = ($link, isExpanded) => {
				$link.attr('aria-expanded', isExpanded);
				const textNode = $link.contents().filter(function() {
					return this.nodeType === 3;
				}).first();

				const newText = isExpanded ? 'Hide Details' : 'Show Details';
				if (textNode.length) {
					textNode[0].nodeValue = `${newText} `;
				} else {
					$link.prepend(`${newText} `);
				}
			};

			$(document)
				.off('click.readingHistory', '.reading-history-toggle-details')
				.on('click.readingHistory', '.reading-history-toggle-details', function(e) {
					e.preventDefault();
					const $link = $(this);
					const targetId = $link.data('target');
					const $target = $('#' + targetId);

					if ($target.length) {
						const shouldExpand = !$target.hasClass('in');
						$target.collapse(shouldExpand ? 'show' : 'hide');
						updateToggleLabel($link, shouldExpand);
					}
				});

			$(document)
				.off('shown.bs.collapse.readingHistory hidden.bs.collapse.readingHistory', '.reading-history-details')
				.on('shown.bs.collapse.readingHistory hidden.bs.collapse.readingHistory', '.reading-history-details', function(e) {
					const targetId = $(this).attr('id');
					if (!targetId) {
						return;
					}
					const isExpanded = e.type === 'shown';
					const $link = $(`.reading-history-toggle-details[data-target="${targetId}"]`);
					if ($link.length) {
						updateToggleLabel($link, isExpanded);
					}
				});
		},

		toggleSelectionMode() {
			const $selectTitle = $('.selectTitle');
			const isSelectionMode = $selectTitle.is(':visible');

			if (isSelectionMode) {
				$selectTitle.hide().removeClass('col-xs-1');
				$selectTitle.prop('checked', false);

				$('.coverColumn').removeClass('col-xs-2 col-sm-3').addClass('col-xs-3 col-sm-4');
				$('.titleColumn').removeClass('col-xs-9 col-sm-8 col-md-9').addClass('col-xs-9 col-sm-8 col-md-10');
				$('.titleColumn.col-xs-11').removeClass('col-xs-11').addClass('col-xs-12');

				$('#selectItemsBtn').show();
				$('#deleteAllBtn').show();
				$('#cancelSelectionBtn').hide();
				$('#deleteDropdown').hide();
			} else {
				$selectTitle.show().addClass('col-xs-1');
				$('.coverColumn').removeClass('col-xs-3 col-sm-4').addClass('col-xs-2 col-sm-3');
				$('.titleColumn').removeClass('col-xs-9 col-sm-8 col-md-10').addClass('col-xs-9 col-sm-8 col-md-9');
				$('.titleColumn.col-xs-12').removeClass('col-xs-12').addClass('col-xs-11');

				$('#selectItemsBtn').hide();
				$('#deleteAllBtn').hide();
				$('#cancelSelectionBtn').show();
				$('#deleteDropdown').show();
			}

			return false;
		},

		deleteEntry(patronId, id) {
			AspenDiscovery.confirm(
				'Delete Reading History Entry',
				'The item will be irreversibly deleted from your reading history. Proceed?',
				'Delete',
				'Cancel',
				true,
				`AspenDiscovery.Account.ReadingHistory.doDeleteEntry(${patronId}, ${id})`,
				'btn-danger'
			);

			return false;
		},

		doDeleteEntry(patronId, id) {
			const url = `${Globals.path}/MyAccount/AJAX`;
			const params = {
				method: 'deleteReadingHistoryEntry',
				patronId,
				entryId: id
			};

			$.getJSON(url, params)
				.done((data) => {
					if (data.success) {
						$(`#readingHistoryEntry${id}`).hide();
						AspenDiscovery.showMessage(data.title, data.message, true);
					} else {
						AspenDiscovery.showMessageWithButtons(data.title, data.message, '', false, '', false, false, false);
					}
				})
			.fail(AspenDiscovery.ajaxFail);

			return false;
		},

		deleteGroupedEntry(patronId, groupedWorkPermanentId, title, author, displayId) {
			AspenDiscovery.confirm(
				'Delete Reading History Entry',
				'All checkout records for this title will be irreversibly deleted from your reading history. Proceed?',
				'Delete',
				'Cancel',
				true,
				`AspenDiscovery.Account.ReadingHistory.doDeleteGroupedEntry(&quot;${patronId}&quot;, &quot;${groupedWorkPermanentId}&quot;, &quot;${title}&quot;, &quot;${author}&quot;, &quot;${displayId}&quot;)`,
				'btn-danger'
			);

			return false;
		},

		doDeleteGroupedEntry(patronId, groupedWorkPermanentId, title, author, displayId) {
			const url = `${Globals.path}/MyAccount/AJAX`;
			const params = {
				method: 'deleteGroupedReadingHistoryEntry',
				patronId,
				groupedWorkPermanentId,
				title,
				author
			};

			$.getJSON(url, params)
				.done((data) => {
					if (data.success) {
						$(`#readingHistoryEntry${displayId}`).fadeOut();
						AspenDiscovery.showMessage(data.title, data.message, true);
					} else {
						AspenDiscovery.showMessageWithButtons(data.title, data.message, '', false, '', false, false, false);
					}
				})
			.fail(AspenDiscovery.ajaxFail);

			return false;
		},

		deleteIndividualEntry(patronId, entryId, groupId) {
			AspenDiscovery.confirm(
				'Delete Checkout Record',
				'This checkout record will be irreversibly deleted from your reading history. Proceed?',
				'Delete',
				'Cancel',
				true,
				`AspenDiscovery.Account.ReadingHistory.doDeleteIndividualEntry(&quot;${patronId}&quot;, &quot;${entryId}&quot;, &quot;${groupId}&quot;)`,
				'btn-danger'
			);

			return false;
		},

		doDeleteIndividualEntry(patronId, entryId, groupId) {
			const url = `${Globals.path}/MyAccount/AJAX`;
			const params = {
				method: 'deleteReadingHistoryEntry',
				patronId,
				entryId
			};

			$.getJSON(url, params)
				.done((data) => {
					if (data.success) {
						// Remove the individual row from the details table
						$(`#readingHistoryDetailEntry${entryId}`).fadeOut(function() {
							$(this).remove();

							// Check if there are any remaining detail rows for this group
							const remainingRows = $(`#readingHistoryDetails${groupId} tbody tr:visible`).length;

							if (remainingRows === 0) {
								// If no rows left, hide the entire grouped entry
								$(`#readingHistoryEntry${groupId}`).fadeOut();
							} else {
								// Update the "checked out X times" count
								const countText = remainingRows === 1 ? 'Checked out 1 time' : `Checked out ${remainingRows} times`;
								$(`#readingHistoryEntry${groupId} .reading-history-count-text`).text(countText);

								// If only one remains, hide the details section and count badge
								if (remainingRows === 1) {
									$(`#readingHistoryDetails${groupId}`).collapse('hide');
									$(`#readingHistoryEntry${groupId} .reading-history-meta`).hide();
								}
							}
						});
						AspenDiscovery.showMessage(data.title, data.message, true);
					} else {
						AspenDiscovery.showMessageWithButtons(data.title, data.message, '', false, '', false, false, false);
					}
				})
			.fail(AspenDiscovery.ajaxFail);

			return false;
		},

		deleteSelectedAction() {
			const selectedItems = $('.titleSelect:checked');
			if (selectedItems.length === 0) {
				AspenDiscovery.showMessageWithButtons('Failed to Delete Reading History Entries', 'Please select one or more items to delete.', '', false, '', false, false, false);
				return false;
			}

			AspenDiscovery.confirm(
				'Delete Selected Items',
				`You have selected ${selectedItems.length} item(s) to delete from your reading history. This action is irreversible. Proceed?`,
				'Delete',
				'Cancel',
				true,
				'AspenDiscovery.Account.ReadingHistory.doDeleteSelected()',
				'btn-danger'
			);
			return false;
		},

		doDeleteSelected() {
			const selectedIds = [];
			// noinspection JSCheckFunctionSignatures
			$('.titleSelect:checked').each(function() {
				const name = $(this).attr('name');
				const match = name.match(/selected\[(\d+)\]/);
				if (match && match[1]) {
					selectedIds.push(match[1]);
				}
			});

			const url = `${Globals.path}/MyAccount/AJAX`;
			const params = {
				method: 'deleteSelectedReadingHistoryEntries',
				patronId: $('#patronId').val(),
				ids: selectedIds
			};

			$.getJSON(url, params)
				.done((data) => {
					if (data.success) {
						selectedIds.forEach((id) => {
							$(`#readingHistoryEntry${id}`).fadeOut();
						});
						AspenDiscovery.showMessageWithButtons(data.title, data.message, '', false, '', false, false, false);
					} else {
						AspenDiscovery.showMessageWithButtons(data.title || 'Error', data.message || 'Failed to delete selected items.', '', false, '', false, false, false);
					}
				})
				.fail(AspenDiscovery.ajaxFail);
		},

		deleteAllAction(){
			if (confirm('Your entire reading history will be irreversibly deleted.  Proceed?')){
				$('#readingHistoryAction').val('deleteAll');
				$('#readingListForm').trigger('submit');
			}
			return false;
		},

		optOutAction: function (){
			if (confirm('Opting out of Reading History will also delete your entire reading history irreversibly.  Proceed?')){
				$('#readingHistoryAction').val('optOut');
				$('#readingListForm').trigger('submit');
			}
			return false;
		},

		optInAction: function (){
			$('#readingHistoryAction').val('optIn');
			$('#readingListForm').trigger('submit');
			return false;
		},

		exportListAction: function (){
			document.location.href = Globals.path + "/MyAccount/AJAX?method=exportReadingHistory";
			return false;
		}
	};
}(AspenDiscovery.Account.ReadingHistory || {}));
