<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tab System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .tabs {
            display: flex;
            cursor: pointer;
            border-bottom: 2px solid #ccc;
        }
        .tab {
            padding: 10px 20px;
            border: 1px solid #ccc;
            border-bottom: none;
            margin-right: 5px;
            background-color: #f9f9f9;
        }
        .tab.active {
            background-color: #fff;
            border-top: 2px solid #007BFF;
            border-left: 2px solid #007BFF;
            border-right: 2px solid #007BFF;
            font-weight: bold;
        }
        .tab-content {
            display: none;
            border: 1px solid #ccc;
            padding: 20px;
            background-color: #fff;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>

<div class="tabs">
    <div class="tab active" data-tab="1">Tab 1</div>
    <div class="tab" data-tab="2">Tab 2</div>
    <div class="tab" data-tab="3">Tab 3</div>
</div>

<div class="tab-content active" id="tab-1">
    <h2>Content for Tab 1</h2>
    <p>This is the content of Tab 1.</p>
</div>
<div class="tab-content" id="tab-2">
    <h2>Content for Tab 2</h2>
    <p>This is the content of Tab 2.</p>
</div>
<div class="tab-content" id="tab-3">
    <h2>Content for Tab 3</h2>
    <p>This is the content of Tab 3.</p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabs = document.querySelectorAll('.tab');
        const contents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.getAttribute('data-tab');

                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                contents.forEach(content => {
                    if (content.id === `tab-${target}`) {
                        content.classList.add('active');
                    } else {
                        content.classList.remove('active');
                    }
                });
            });
        });
    });
</script>

</body>
</html>
