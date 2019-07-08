function getFluRecords(callback) {
        //Request Data
        $.ajax({
                url: '/updatedata.php',
                type: 'post',
                data: {'col': "fetchrecords"},
                success: function(data, status) {
                        data = JSON.parse(data);
                        callback(data);
                },
                error: function(xhr, desc, err) {
                        console.log(xhr);
                        console.log("Details: " + desc + "\nError:" + err);
                }
        });
}

function updateDBRecords(data) {
        //Change Data
        var data = data.split(",");
        var casename = data[0];
        var type = data[1];
        var seq = data[2];        
        var clade = data[3];

        if(clade != "") {
            $.ajax({
                    url: '/updatedata.php',
                    type: 'post',
                    data: {'col': "updaterecords", 'seq': seq, 'clade': clade, 'type': type, 'casename': casename},
                    success: function(data, status) {
                            console.log(data);
                    },
                    error: function(xhr, desc, err) {
                            console.log(xhr);
                            console.log("Details: " + desc + "\nError:" + err);
                    }
            });
        }
}
