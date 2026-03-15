


<div class="modal fade" id="barcodeReturnModal" tabindex="-1"
     data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Book Return</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="modalCloseButton"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div style="font-size:3rem;"></div>
                    <h4 id="returnBookTitle" class="text-primary fw-bold mt-2"></h4>
                    <p id="returnBookAuthor" class="text-muted mb-0"></p>
                </div>

                <div class="p-3 rounded mb-3" style="background:#f8f9fa;">
                    <h6 class="fw-bold text-success">Borrower Information</h6>
                    <p class="mb-1"><strong>Name:</strong> <span id="borrowerNameText"></span></p>
                    <p class="mb-1"><strong>Course & Section:</strong> <span id="borrowerCourseText"></span></p>
                    <p class="mb-0"><strong>Due Date:</strong> <span id="dueDateText"></span></p>
                </div>

                <div class="status-processing-area">
                    <div class="checkbox-container">
                        <input type="checkbox" id="confirmReturnCheckbox">
                        <label for="confirmReturnCheckbox">I confirm the physical book is returned</label>
                    </div>
                    <div id="statusBorrowed"   class="status-borrowed">Status: Currently Borrowed — Ready for Return</div>
                    <div id="statusProcessing" class="status-processing d-none">
                        Status: Processing Return…
                        <div class="processing-indicator">
                            <div class="spinner"></div><span>Processing…</span>
                        </div>
                    </div>
                    <div id="statusCompleted"  class="status-completed d-none">Status: Return Completed ✅</div>
                </div>

                <div class="alert alert-warning mt-3 mb-0">
                    ⚠️ Please verify the physical book is being returned before confirming.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelReturnBtn">Cancel</button>
                <form id="barcodeReturnForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-success" id="confirmReturnBtn" disabled>✅ Confirm Return</button>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="borrowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Borrow Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <h5 id="borrowBookTitle">Loading…</h5>
                    <small id="borrowBookAuthor" class="text-muted"></small>
                </div>
                <form id="borrowByBarcodeForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="book_copy_barcode" id="borrow_copy_barcode">
                    <div class="mb-2">
                        <input type="text" name="student_name" id="borrow_student_name"
                               class="form-control" placeholder="Student Name" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="course" id="borrow_course"
                               class="form-control" placeholder="Course" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="section" id="borrow_section"
                               class="form-control" placeholder="Section" required>
                    </div>
                    <div id="borrowAlert" class="alert d-none" role="alert"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="borrowSubmitBtn" class="btn btn-success">Confirm Borrow</button>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\xampp\htdocs\PROJECT-NAME-main\resources\views/partials/modals.blade.php ENDPATH**/ ?>