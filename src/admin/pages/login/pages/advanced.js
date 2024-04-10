/* WordPress */
import { __ } from '@wordpress/i18n';
import { useContext, useState, useEffect } from '@wordpress/element';

/* Library */
import classNames from 'classnames';

import { cloneDeep } from 'lodash';

/*Atrc*/
import {
	AtrcText,
	AtrcWireFrameContentSidebar,
	AtrcWireFrameHeaderContentFooter,
	AtrcPrefix,
	AtrcPanelBody,
	AtrcPanelRow,
	AtrcContent,
	AtrcTitleTemplate1,
	AtrcControlCodeTextarea,
} from 'atrc';

/* Inbuilt */
import { AtrcReduxContextData } from '../../../routes';
import { DocsTitle } from '../../../components/molecules';

/*Local*/
const MainContent = () => {
	const data = useContext(AtrcReduxContextData);

	const { dbSettings, dbUpdateSetting } = data;

	const { login = {} } = dbSettings;
	const { css = '', js = '' } = login;

	/* Fixing change */
	const [cssState, setCssState] = useState(css);
	const [jsState, setJsState] = useState(js);

	useEffect(() => {
		const loginCloned = cloneDeep(login);
		loginCloned.css = cssState;
		loginCloned.js = jsState;
		dbUpdateSetting('login', loginCloned);
	}, [cssState, jsState]);

	return (
		<AtrcContent>
			<AtrcPanelRow className={classNames('at-m')}>
				<AtrcControlCodeTextarea
					label={__('Custom CSS', 'brand-master')}
					value={css}
					notice={false}
					onChange={setCssState}
				/>
			</AtrcPanelRow>
			<AtrcPanelRow className={classNames('at-m')}>
				<AtrcControlCodeTextarea
					label={__('Custom JavaScript', 'brand-master')}
					value={js}
					notice={false}
					editorSettings={{
						indentUnit: 2,
						tabSize: 2,
						mode: 'js',
					}}
					onChange={setJsState}
				/>
			</AtrcPanelRow>
		</AtrcContent>
	);
};

const Documentation = () => {
	const data = useContext(AtrcReduxContextData);

	const { lsSettings, lsSaveSettings } = data;

	return (
		<AtrcWireFrameHeaderContentFooter
			headerRowProps={{
				className: classNames(AtrcPrefix('header-docs'), 'at-m'),
			}}
			renderHeader={
				<DocsTitle
					onClick={() => {
						const localStorageClone = cloneDeep(lsSettings);
						localStorageClone.bmLaDocs1 = !localStorageClone.bmLaDocs1;
						lsSaveSettings(localStorageClone);
					}}
				/>
			}
			renderContent={
				<>
					<AtrcPanelBody
						className={classNames(AtrcPrefix('m-0'))}
						title={__('Custom CSS:', 'brand-master')}
						initialOpen={true}>
						<AtrcText
							tag='p'
							className={classNames(AtrcPrefix('m-0'), 'at-m')}>
							{__(
								"Tailor your login page's appearance with custom CSS. Write standard code to adjust colors, fonts, and layout, creating a personalized and visually appealing design.",
								'brand-master'
							)}
						</AtrcText>
					</AtrcPanelBody>
					<AtrcPanelBody
						title={__('Custom JS:', 'brand-master')}
						initialOpen={false}>
						<AtrcText
							tag='p'
							className={classNames(AtrcPrefix('m-0'), 'at-m')}>
							{__(
								'Enhance login page functionality with custom JavaScript. Implement custom behaviors and interactive elements to elevate the user experience, aligning the page with your specific requirements.',
								'brand-master'
							)}
						</AtrcText>
					</AtrcPanelBody>
				</>
			}
			allowHeaderRow={false}
			allowHeaderCol={false}
			allowContentRow={false}
			allowContentCol={false}
		/>
	);
};

const Settings = () => {
	const data = useContext(AtrcReduxContextData);
	const { lsSettings } = data;

	const { bmLaDocs1 } = lsSettings;

	return (
		<AtrcWireFrameHeaderContentFooter
			wrapProps={{
				className: classNames(AtrcPrefix('bg-white'), 'at-bg-cl'),
			}}
			renderHeader={
				<AtrcTitleTemplate1
					title={__('WordPress login page settings', 'brand-master')}
				/>
			}
			renderContent={
				<AtrcWireFrameContentSidebar
					wrapProps={{
						allowContainer: true,
						type: 'fluid',
						tag: 'section',
						className: 'at-p',
					}}
					renderContent={<MainContent />}
					renderSidebar={!bmLaDocs1 ? <Documentation /> : null}
					contentProps={{
						contentCol: bmLaDocs1 ? 'at-col-12' : 'at-col-7',
					}}
					sidebarProps={{
						sidebarCol: 'at-col-5',
					}}
				/>
			}
			allowHeaderRow={false}
			allowHeaderCol={false}
			allowContentRow={false}
			allowContentCol={false}
		/>
	);
};

export default Settings;
