&middot; <a data-toggle="modal" href="#myModal">Select Language</a>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Select Language</h4>
            </div>
            <div class="modal-body">
                <?php
                $path = app_path().'/lang';
                $results = scandir($path);
                foreach ($results as $result) {
                    if ($result === '.' or $result === '..') continue;
                    if (is_dir($path . '/' . $result)) {
                        echo '<p> <a href="'.url('lang/'.$result).'">'.langDecode($result).'</a>';
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>