<?php
// file: src/app/pulse/views/matrix.php
// MIT License

namespace bravedave\pulse;

use bravedave\dvc\strings; ?>

<form class="col-auto d-flex align-items-center" id="<?= $_form = strings::rand() ?>">

  <div class="row g-2 mb-2 d-print-none">

    <div class="col-lg">
      <div class="input-group">
        <input type="search" accesskey="/" class="form-control"
          id="<?= $_search = strings::rand() ?>" autofocus>
      </div>
    </div>

    <div class="col col-lg-auto">

      <div class="input-group">

        <input type="date" class="form-control" id="<?= $_dateFrom = strings::rand() ?>"
          value="<?= $from ?>" placeholder="From">
        <div class="input-group-text">-</div>
        <input type="date" class="form-control" id="<?= $_dateTo = strings::rand() ?>"
          value="<?= $to ?>" placeholder="To">
        <button type="submit" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-clockwise"></i>
        </button>
      </div>
    </div>

    <div class="col-auto">
      <button class="btn btn-outline-primary" id="<?= $_uidAdd = strings::rand() ?>">
        <i class="bi bi-plus-circle"></i> new
      </button>
    </div>
  </div>
</form>

<div id="<?= $_blog = strings::rand() ?>"></div>
<script>
  (_ => {
    const blog = $('#<?= $_blog ?>');
    const search = $('#<?= $_search ?>');
    const form = $('#<?= $_form ?>');

    const contextmenu = function(e) {

      if (e.shiftKey) return;
      const _ctx = _.context(e); // hides any open contexts and stops bubbling

      _ctx.append.a({
        html: '<i class="bi bi-pencil"></i>edit',
        click: e => $(this).trigger('edit')
      });

      _ctx.append.a({
        html: '<i class="bi bi-trash"></i>delete',
        click: e => $(this).trigger('delete')
      });

      _ctx.open(e);
    };

    const edit = function() {

      _.get.modal(_.url(`<?= $this->route ?>/edit/${this.dataset.id}`))
        .then(m => m.on('success', e => $(this).trigger('refresh')));
    };

    const getMatrix = () => new Promise((resolve, reject) => {

      const from = $('#<?= $_dateFrom ?>').val();
      const to = $('#<?= $_dateTo ?>').val();

      _.fetch.post(_.url('<?= $this->route ?>'), {
        action: 'get-matrix',
        from: from,
        to: to
      }).then(d => ('ack' == d.response) ? matrix(d.data) : _.growl(d));
    });

    const matrix = data => {

      blog.empty();

      $.each(data, (i, dto) => {

        const formatDate = dateStr => {
          const date = _.dayjs(dateStr);
          const today = _.dayjs();

          if (
            date.isSame(today, 'day')
          ) {
            return date.format('HH:mm');
          }

          // console.log(date, today);
          return date.format('L');
        };

        dto._createdDisplay = formatDate(dto.created);
        dto._updatedDisplay = formatDate(dto.updated);


        $(`<article class="card mb-4 pointer" data-id="${dto.id}">
            <div class="card-header">
              <h5 class="card-title mb-0">${dto.title}</h5>
            </div>
            <div class="card-body">
              <div class="card-text">${dto.content}</div>
            </div>
            <div class="card-footer text-muted d-flex justify-content-end">
              <em class="js-updated">created : ${formatDate(dto.created)} </em>
              /
              <em class="js-updated"> updated : ${formatDate(dto.updated)}</em>
            </div>
          </article>`)
          .on('click', function(e) {

            e.stopPropagation();
            $(this).trigger('edit');
          })
          .on('contextmenu', contextmenu)
          .on('delete', rowDelete)
          .on('edit', edit)
          .on('refresh', rowRefresh)
          .appendTo(blog);
      });
    };

    const refresh = () => getMatrix().then(matrix).catch(_.growl);

    const rowDelete = function(e) {

      e.stopPropagation();

      _.fetch
        .post(_.url('<?= $this->route ?>'), {
          action: 'pulse-delete',
          id: this.dataset.id
        })
        .then(d => ('ack' == d.response) ? this.remove() : _.growl(d));
    };

    const rowRefresh = function(e) {
      e.stopPropagation();

      const row = $(this);

      _.fetch.post(_.url('<?= $this->route ?>'), {
        action: 'get-by-id',
        id: this.dataset.id
      }).then(d => {

        if ('ack' == d.response) {

          row.find('.card-header').html(d.data.title);
          row.find('.card-body').html(d.data.content);
          row.find('.js-updated').html(d.data.updated);
        } else {

          _.growl(d);
        }
      });
    };

    form.on('submit', e => {

      e.preventDefault();
      getMatrix()
        .then(matrix)
        .catch(_.growl);
    });

    search.on('input', function() {
      const val = this.value.trim();
      if (!val) {
        blog.children('article').removeClass('d-none');
        return;
      }
      const re = new RegExp(val, 'i');
      blog.children('article').each(function() {
        const $a = $(this);
        const text = $a.text();
        if (re.test(text)) {
          $a.removeClass('d-none');
        } else {
          $a.addClass('d-none');
        }
      });
    });

    $('#<?= $_uidAdd ?>').on('click', function(e) {

      _.hideContexts(e);

      _.get.modal(_.url('<?= $this->route ?>/edit'))
        .then(m => m.on('success', e => refresh()));
    });

    _.ready(() => refresh());
  })(_brayworth_);
</script>