<?php /** Reusable confirm modal (Dashly), driven by JS. */ ?>
<div class="modal fade" id="confirmActionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-card card">
                <div class="card-header">
                    <h4 class="card-header-title" id="confirmActionModalTitle">Confirm Action</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="avatar avatar-lg mb-4" style="width:6rem;height:6rem;">
                            <div class="avatar-title fs-1 bg-danger-subtle rounded-circle text-danger" style="font-size:3rem !important;">
                                <i class="fe fe-alert-triangle"></i>
                            </div>
                        </div>
                        <p id="confirmActionMessage" class="mb-4 lead text-center"></p>
                        <button type="button" class="btn btn-danger btn-lg mb-4" id="confirmActionButton">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
