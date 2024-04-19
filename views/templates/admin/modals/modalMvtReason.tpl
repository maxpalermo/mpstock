<div class="modal fade" id="ModalMvtReason" tabindex="-1" role="dialog" aria-labelledby="ModalMvtReasonLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalMvtReasonLabel">Tipo di Movimento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group fixed-width-md">
                            <label for="mvt_reason_id">Codice Movimento</label>
                            <input id="mvt_reason_id" class="form-control text-right" type="text" name="mvt_reason_id"
                                value="0">
                        </div>
                        <div class="form-group">
                            <label for="mvt_reason_name">Descrizione</label>
                            <input id="mvt_reason_name" class="form-control" type="text" name="mvt_reason_name"
                                maxlength="128">
                        </div>
                        <div class="form-group fixed-width-lg">
                            <label for="type">Tipo di movimento</label>
                            <select id="mvt_reason_sign" class="form-select" name="mvt_reason_sign">
                                <option value="0">Positivo</option>
                                <option value="1">Negativo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="icon icon-times"></i>
                        <span>Chiudi</span>
                    </button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal"
                        onclick="javascript:saveMovement();">
                        <i class="icon icon-save"></i>
                        <span>Salva</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalMvtReasonDelete" tabindex="-1" role="dialog"
    aria-labelledby="ModalMvtReasonDeleteLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <h5>Sei sicuro di voler eliminare il movimento?</h5>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="icon icon-times"></i>
                    <span>Chiudi</span>
                </button>
                <button type="button" class="btn btn-danger">
                    <i class="icon icon-trash"></i>
                    <span>Elimina</span>
                </button>
            </div>
        </div>
    </div>
</div>