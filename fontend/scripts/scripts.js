// document.createElement
// document.getElementsByTagName
// document.createElement
// language
// filname
// code

async function send_review() {
    const payload = {
        language: document.getElementById('language').value,
        filename: document.getElementById('filename').value,
        code: document.getElementById('code').value
    };

    const response = await fetch('review.php', {
        method : 'POST',
        header: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    });

    const response_data = response.json();
    //validate response later

    //build html table to return: th=column, tr=row, td=cell
    let result_html=`<table><tr>Severity</tr><tr>File</tr><tr>Issue</tr><tr>Suggestions</tr>`;

    response_data.array.forEach(element => { //array
        result_html += `<tr>
            <td class="${element.severity}">${element.severity}</td>
            <td>${element.file}/td>
            <td>${element.issue}</td>
            <td>${element.suggestion}</td>
        </tr>`;
    });
    result_html+=`</table>`;

    document.getElementById('result').innerHTML = result_html;
}